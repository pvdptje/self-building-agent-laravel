<?php

// Enhanced with more mappings by agent.

$toolDefinition_text_emoji_convert = array (
  'type' => 'function',
  'function' => 
  array (
    'name' => 'text_emoji_convert',
    'description' => 'Convert text patterns to emoji or emoji to descriptive text. Supports sentiment-to-emoji, word-to-emoji mapping, and emoji-to-text decoding.',
    'parameters' => 
    array (
      'type' => 'object',
      'properties' => 
      array (
        'input' => 
        array (
          'type' => 'string',
          'description' => 'Text or emoji string to convert',
        ),
        'direction' => 
        array (
          'type' => 'string',
          'description' => "'to_emoji' - text patterns -> emoji, 'from_emoji' - emoji -> description (default: 'to_emoji')",
        ),
      ),
      'required' => 
      array (
        0 => 'input',
      ),
    ),
  ),
);

if (! function_exists('text_emoji_convert')) {
    function text_emoji_convert($input, $direction = null)
    {
        $emojiMap = [
            // Classic emotions
            'happy' => '😊', 'joy' => '🎉', 'sad' => '😢', 'love' => '❤️',
            'fire' => '🔥', 'star' => '⭐', 'rocket' => '🚀', 'check' => '✅',
            'cross' => '❌', 'warning' => '⚠️', 'info' => 'ℹ️', 'question' => '❓',
            'lightbulb' => '💡', 'brain' => '🧠', 'gear' => '⚙️', 'tool' => '🔧',
            'book' => '📖', 'write' => '✍️', 'code' => '💻', 'data' => '📊',
            'music' => '🎵', 'mountain' => '⛰️', 'ocean' => '🌊', 'sun' => '☀️',
            'moon' => '🌙', 'tree' => '🌳', 'flower' => '🌸', 'heart' => '💖',
            'thumbsup' => '👍', 'clap' => '👏', 'wave' => '👋', 'eye' => '👁️',
            'key' => '🔑', 'lock' => '🔒', 'bell' => '🔔', 'clock' => '⏰',
            'money' => '💰', 'gift' => '🎁', 'pencil' => '✏️', 'magnify' => '🔍',
            // Extended set
            'smile' => '😄', 'laugh' => '😂', 'cool' => '😎', 'wink' => '😉',
            'think' => '🤔', 'sleep' => '😴', 'angry' => '😠', 'cry' => '😭',
            'surprise' => '😮', 'kiss' => '😘', 'party' => '🎊', 'celebration' => '🎉',
            'rainbow' => '🌈', 'cloud' => '☁️', 'rain' => '🌧️', 'snow' => '❄️',
            'lightning' => '⚡', 'tornado' => '🌪️', 'comet' => '☄️', 'planet' => '🌍',
            'seed' => '🌱', 'leaf' => '🍃', 'mushroom' => '🍄', 'cactus' => '🌵',
            'pizza' => '🍕', 'burger' => '🍔', 'fries' => '🍟', 'coffee' => '☕',
            'tea' => '🍵', 'beer' => '🍺', 'wine' => '🍷', 'cake' => '🎂',
            'robot' => '🤖', 'alien' => '👽', 'ghost' => '👻', 'skull' => '💀',
            'crystal' => '🔮', 'magic' => '✨', 'bomb' => '💣', 'microscope' => '🔬',
            'telescope' => '🔭', 'satellite' => '📡', 'antenna' => '📶', 'battery' => '🔋',
            'plug' => '🔌', 'lamp' => '💡', 'printer' => '🖨️', 'camera' => '📷',
            'movie' => '🎬', 'theater' => '🎭', 'art' => '🎨', 'palette' => '🎨',
            'train' => '🚂', 'airplane' => '✈️', 'car' => '🚗', 'bicycle' => '🚲',
            'anchor' => '⚓', 'compass' => '🧭', 'map' => '🗺️', 'globe' => '🌐',
            'trophy' => '🏆', 'medal' => '🏅', 'crown' => '👑', 'gem' => '💎',
        ];

        $reverseMap = [];
        foreach ($emojiMap as $word => $emoji) {
            $reverseMap[$emoji] = $word;
        }

        if ($direction === 'from_emoji') {
            $result = $input;
            foreach ($reverseMap as $emoji => $word) {
                $result = str_replace($emoji, ":$word:", $result);
            }
            return json_encode(['input' => $input, 'output' => $result, 'direction' => 'from_emoji']);
        }

        // to_emoji: replace words with emoji
        $result = $input;
        $replacements = [];
        foreach ($emojiMap as $word => $emoji) {
            $pattern = '/\b' . preg_quote($word, '/') . '\b/i';
            $newResult = preg_replace($pattern, $emoji, $result);
            if ($newResult !== $result) {
                $replacements[] = $word;
                $result = $newResult;
            }
        }
        return json_encode([
            'input' => $input,
            'output' => $result,
            'direction' => 'to_emoji',
            'replacements_made' => $replacements,
            'count' => count($replacements)
        ]);
    }
}
