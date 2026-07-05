<?php
$toolDefinition_ascii_fractal_generator = array('type'=>'function','function'=>array('name'=>'ascii_fractal_generator','description'=>'Generate ASCII art of classic fractals: Sierpinski triangle, Cantor set, fractal tree, or dragon curve. Pure function, no side effects.','parameters'=>array('type'=>'object','properties'=>array('fractal'=>array('type'=>'string','description'=>'sierpinski/cantor/tree/dragon (default sierpinski)'),'size'=>array('type'=>'integer','description'=>'Size parameter. Default 32.'),'fill_char'=>array('type'=>'string','description'=>'Fill character (default #)'),'bg_char'=>array('type'=>'string','description'=>'Background character (default space)')),'required'=>array())));
if (!function_exists('ascii_fractal_generator')) {
function ascii_fractal_generator($fractal=null,$size=null,$fill_char=null,$bg_char=null){
$f=$fractal??'sierpinski';$s=$size??32;$c=$fill_char??'#';$bg=$bg_char??' ';
switch($f){
case'sierpinski':
$r=max(8,min(64,(int)$s));$r=(int)pow(2,floor(log($r,2)));$cols=$r*2-1;$g=[];for($i=0;$i<$r;$i++){$g[$i]=array_fill(0,$cols,$bg);}
$dr=function($x,$y,$sz)use(&$dr,&$g,$c){if($sz<=1){if(isset($g[$y][$x]))$g[$y][$x]=$c;return;}$h=(int)($sz/2);$dr($x,$y,$h);$dr($x-$h,$y+$h,$h);$dr($x+$h,$y+$h,$h);};
$dr((int)($cols/2),0,$r);$o=implode("\n",array_map(function($r){return implode('',$r);},$g));
break;
case'cantor':
$it=max(1,min(8,(int)$s));$w=(int)pow(3,$it);$g=array_fill(0,$it,str_repeat($c,$w));
for($l=0;$l<$it;$l++){$seg=max(1,(int)($w/pow(3,$l)));$line=str_split($g[$l]);
for($p=0;$p<$w;$p+=$seg){$start=$p+(int)($seg/3);$end=$p+(int)(2*$seg/3);for($i=$start;$i<$end&&$i<$w;$i++)$line[$i]=$bg;}
$g[$l]=implode('',$line);if($l+1<$it)$g[$l+1]=$g[$l];}
$o=implode("\n",$g);
break;
case'tree':
$d=max(5,min(12,(int)$s));$h=(int)pow(2,$d);$w=$h*2+1;$cvs=array_fill(0,$h,array_fill(0,$w,$bg));
$br=function($x,$y,$len,$angle,$dep)use(&$br,&$cvs,$c,$h,$w){
if($dep<=0||$len<1)return;$ex=$x+(int)($len*cos($angle));$ey=$y-(int)($len*sin($angle));
$steps=max(abs($ex-$x),abs($ey-$y));
for($i=0;$i<=$steps;$i++){$px=(int)($x+($ex-$x)*$i/$steps);$py=(int)($y+($ey-$y)*$i/$steps);if(isset($cvs[$py][$px]))$cvs[$py][$px]=$c;}
$br($ex,$ey,$len*0.7,$angle-M_PI/5,$dep-1);$br($ex,$ey,$len*0.7,$angle+M_PI/5,$dep-1);};
$br((int)($w/2),$h-1,(int)($h*0.4),M_PI/2,$d);
$o=implode("\n",array_map(function($r){return implode('',$r);},$cvs));
break;
case'dragon':
$it=max(5,min(15,(int)$s));$pts=[[0,0],[1,0]];
for($i=0;$i<$it;$i++){$np=$pts;$last=$pts[count($pts)-1];for($j=count($pts)-2;$j>=0;$j--){$dx=$pts[$j][0]-$last[0];$dy=$pts[$j][1]-$last[1];$np[]=[$last[0]-$dy,$last[1]+$dx];}$pts=$np;}
$minX=$maxX=$minY=$maxY=0;foreach($pts as$p){$minX=min($minX,$p[0]);$maxX=max($maxX,$p[0]);$minY=min($minY,$p[1]);$maxY=max($maxY,$p[1]);}
$pw=$maxX-$minX+1;$ph=$maxY-$minY+1;$scale=max($pw,$ph)>100?100/max($pw,$ph):1;
$rh=(int)($ph*$scale)+1;$rw=(int)($pw*$scale)+1;$cvs=array_fill(0,$rh,array_fill(0,$rw,$bg));
foreach($pts as$p){$px=(int)(($p[0]-$minX)*$scale);$py=(int)(($p[1]-$minY)*$scale);if(isset($cvs[$py][$px]))$cvs[$py][$px]=$c;}
$o=implode("\n",array_map(function($r){return implode('',$r);},$cvs));
break;
default:return json_encode(['error'=>"Unknown fractal: $f"]);
}
return json_encode(['fractal'=>$f,'size'=>$s,'ascii'=>$o]);
}}