# Crown of Kings - Winning Playthrough

## Command Sequence (23 steps)
1. `take torch` - Get the torch from Entrance Hall
2. `go north` → Library
3. `take ancient_book` - Get the lore book
4. `read ancient_book` - Learn about the mirror trick
5. `go south` → Entrance Hall
6. `go west` → Overgrown Garden
7. `take magic_mirror` - Get the mirror
8. `use magic_mirror` - Reveal hidden rusty_key in fountain
9. `take rusty_key` - Get the dungeon key
10. `go east` → Entrance Hall
11. `go east` → Kitchen
12. `use torch` - Light torch from hearth fire (REQUIRED for dungeon)
13. `go north` → Armory
14. `take sword` - Get the weapon to fight Guardian
15. `go south` → Kitchen
16. `go west` → Entrance Hall
17. `go north` → Library
18. `go east` → Tall Tower (requires torch in inventory)
19. `go down` → Dark Dungeon (requires rusty_key AND torch_lit)
20. `go east` → Throne Room
21. `use sword` - Defeat the Guardian
22. `take crown` - Pick up the Crown
23. `use crown` - Claim the throne! 🎉

## Key Requirements
- **torch** - Must be in inventory to enter Tower; must be LIT (use in Kitchen) to descend to Dungeon
- **rusty_key** - Unlocks dungeon door (get via mirror puzzle in Garden after reading book)
- **sword** - Defeats the Guardian in Throne Room
- **crown** - Must take it (it's a room item), then USE it after defeating Guardian

## Bug Fixed: game_playthrough_runner.php
The runner was calling the game with `state='new'` which initializes the game and returns immediately, ignoring the command. First command was always silently lost. Fixed by doing a separate initialization call first, then running commands against the returned state.

## Verified: 2025-07-18
