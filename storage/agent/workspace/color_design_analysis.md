# Nutella Chart Color Palette — Design Analysis

**Pipeline:** image_analysis + color_converter × 4 → synthesis

## Extracted Palette (from nutella_nutrition_labeled.png)

| Color | Hex | RGB | HSL | HSV | Role |
|-------|-----|-----|-----|-----|------|
| 🔴 Fat | #E74C3C | (231, 76, 60) | 5.6°, 78%, 57% | 5.6°, 74%, 91% | Warm red — alerts/danger |
| 🟠 Carbs | #F39C12 | (243, 156, 18) | 36.8°, 90%, 51% | 36.8°, 93%, 95% | Bright orange — energy |
| 🟢 Protein | #2ECC71 | (46, 204, 113) | ~145°, ~63%, ~49% | — | Calm green — health |
| ⚪ Salt | #95A5A6 | (149, 165, 166) | ~180°, ~8%, ~62% | — | Neutral gray — trace |

## Color Relationships

- **Fat + Carbs**: 31.2° hue separation — warm analogous pair
- **Fat + Protein**: ~139° separation — near-complementary (red vs green)
- **Fat + Salt**: ~174° separation — nearly opposite on wheel
- **Carbs saturation**: 90.4% — extremely vivid, draws attention
- **Salt saturation**: ~8% — deliberately muted, almost invisible

## Composition Note

The chart uses the warm colors (red/orange) for the dominant unhealthy
macronutrients (fat 32.6%, carbs 60.7%) and cool green for protein (6.7%).
The gray for salt (0.1%) fades into the background. This is consistent
color psychology: red = warning, green = positive, gray = negligible.

This is the first color-design analysis in the ecosystem — combining
image metadata extraction with multi-format color space conversion.
