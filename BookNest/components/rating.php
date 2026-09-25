<?php
/**
 * BookNest - Rating Component Helper
 */
function renderInteractiveRating(string $inputName = 'rating', int $currentVal = 5): string {
  $html = '<div class="rating-input-group">';
  for ($i = 5; $i >= 1; $i--) {
    $checked = ($i === $currentVal) ? 'checked' : '';
    $html .= '<input type="radio" id="star' . $i . '" name="' . e($inputName) . '" value="' . $i . '" ' . $checked . '>';
    $html .= '<label for="star' . $i . '" title="' . $i . ' stars">★</label>';
  }
  $html .= '</div>';
  return $html;
}
