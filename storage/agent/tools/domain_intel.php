<?php
$toolDefinition_domain_intel = array ( 'type' => 'function', 'function' => array ( 'name' => 'domain_intel', 'description' => 'Full domain intelligence report: DNS + WHOIS + SSL cert + IP geolocation in one pass.', 'parameters' => array ( 'type' => 'object', 'properties' => array ( 'domain' => array ( 'type' => 'string', 'description' => 'Domain to analyze.' ), 'timeout' => array ( 'type' => 'integer', 'description' => 'Timeout per source in seconds (5-30). Default: 15.' ) ), 'required' => array ( 'domain' ) ) ) );


if (! function_exists('domain_intel')) {
    function domain_intel($domain, $timeout = null) {
        $timeout = max(5, min(30, (int)($timeout ?? 15)));
        $domain = trim(strtolower($domain));
        $domain = preg_replace('#^https?://#', '', $domain);
        $domain = preg_replace('#^www\.#', '', $domain);
        $domain = preg_replace('#/.*$#', '', $domain);
        if (!preg_match('/^[a-z0-9]([a-z0-9\-]{0,61}[a-z0-9])?(\.[a-z0-9]([a-z0-9\-]{0,61}[a-z0-9])?)*\.[a-z]{2,}$/', $domain)) {
            return [ 'success' => false, 'error' => 'Valid domain name is required.' ];
        }
        $r = [ 'success' => true, 'domain' => $domain, 'sources' => [], 'errors' => [] ];
        
        $fetch = function($url, $t) {
            $ctx = stream_context_create([ 'http' => [ 'method' => 'GET', 'timeout' => $t, 'header' => "User-Agent: di/1.0\r\n", 'ignore_errors' => true ], 'ssl' => [ 'verify_peer' => false, 'verify_peer_name' => false ] ]);
            $body = @file_get_contents($url, false, $ctx);
            if ($body === false) return [ 'error' => 'fetch failed', 'success' => false ];
            $status = 200;
            if (isset($http_response_header)) { foreach ($http_response_header as $h) { if (preg_match('#^HTTP/\d+\.\d+ (\d+)#', $h, $m)) { $status = (int)$m[1]; break; } } }
            $data = @json_decode($body, true);
            return $data ? [ 'data' => $data, 'success' => true ] : [ 'error' => 'Invalid JSON', 'success' => false ];
        };
        
        // DNS
        $dns = [ 'a' => [], 'mx' => [], 'ns' => [], 'txt' => [] ];
        $a = @dns_get_record($domain, DNS_A);
        if (is_array($a)) { $ips = []; foreach ($a as $rec) { if (!empty($rec['ip'])) $ips[] = $rec['ip']; } $dns['a'] = array_values(array_unique($ips)); }
        $mx = @dns_get_record($domain, DNS_MX);
        if (is_array($mx)) { foreach ($mx as $rec) { if (!empty($rec['target'])) $dns['mx'][] = [ 'target' => $rec['target'], 'pri' => (int)($rec['pri'] ?? 0) ]; } }
        $ns = @dns_get_record($domain, DNS_NS);
        if (is_array($ns)) { $nl = []; foreach ($ns as $rec) { if (!empty($rec['target'])) $nl[] = $rec['target']; } $dns['ns'] = array_values(array_unique($nl)); }
        $txt = @dns_get_record($domain, DNS_TXT);
        if (is_array($txt)) { $tl = []; foreach ($txt as $rec) { if (!empty($rec['txt'])) $tl[] = $rec['txt']; } $dns['txt'] = $tl; }
        $r['sources']['dns'] = $dns;
        $pip = $dns['a'][0] ?? null;
        
        // Geo
        if ($pip) {
            $g = $fetch("http://ip-api.com/json/{$pip}", $timeout);
            if ($g['success'] && ($g['data']['status'] ?? '') === 'success') {
                $dd = $g['data']; $r['sources']['geo'] = [ 'ip' => $dd['query'], 'city' => $dd['city'], 'country' => $dd['country'], 'region' => $dd['regionName'], 'isp' => $dd['isp'], 'asn' => $dd['as'] ];
            }
        }
        
        // SSL
        if ($pip) {
            $ctx = stream_context_create([ 'ssl' => [ 'capture_peer_cert' => true, 'verify_peer' => false, 'verify_peer_name' => false ] ]);
            $st = @stream_socket_client("ssl://{$domain}:443", $en, $es, $timeout, STREAM_CLIENT_CONNECT, $ctx);
            if ($st) {
                $opts = stream_context_get_options($ctx);
                $cert = $opts['ssl']['peer_certificate'] ?? null;
                if ($cert) { $cd = @openssl_x509_parse($cert, true); if ($cd) { $vt = $cd['validTo_time_t'] ?? null; $dr = $vt ? (int)ceil(($vt - time()) / 86400) : null; $r['sources']['ssl'] = [ 'cn' => $cd['subject']['CN'] ?? '', 'issuer' => $cd['issuer']['CN'] ?? '', 'days' => $dr, 'expired' => $dr !== null && $dr < 0 ]; } }
                fclose($st);
            }
        }
        
        // WHOIS
        $parts = explode('.', $domain);
        $tld = strtolower(end($parts));
        $wmap = [ 'com' => 'whois.verisign-grs.com', 'net' => 'whois.verisign-grs.com', 'org' => 'whois.pir.org', 'io' => 'whois.nic.io' ];
        $ws = $wmap[$tld] ?? '';
        if ($ws) {
            $sock = @fsockopen($ws, 43, $en, $es, $timeout);
            if ($sock) {
                stream_set_timeout($sock, $timeout); @fwrite($sock, $domain . "\r\n"); $resp = '';
                while (!feof($sock)) { $c = @fread($sock, 8192); if ($c === false) break; $resp .= $c; if (stream_get_meta_data($sock)['timed_out']) break; }
                fclose($sock);
                if (trim($resp)) {
                    $w = [ 'registered' => !(bool)preg_match('/No match for|NOT FOUND/i', $resp) ];
                    $fm = [ 'Domain Name' => 'name', 'Registrar' => 'reg', 'Creation Date' => 'created', 'Registry Expiry Date' => 'expires' ];
                    foreach ($fm as $k => $s) { if (preg_match('/^' . preg_quote($k, '/') . ':\s*(.*)$/mi', $resp, $m)) $w[$s] = trim($m[1]); }
                    $r['sources']['whois'] = $w;
                }
            }
        }
        
        $r['summary'] = [ 'domain' => $domain, 'ip' => $pip, 'ssl_days' => $r['sources']['ssl']['days'] ?? null, 'registered' => $r['sources']['whois']['registered'] ?? null, 'location' => ($r['sources']['geo']['city'] ?? '') . ', ' . ($r['sources']['geo']['country'] ?? ''), 'isp' => $r['sources']['geo']['isp'] ?? null, 'reg' => $r['sources']['whois']['reg'] ?? null ];
        return $r;
    }
}