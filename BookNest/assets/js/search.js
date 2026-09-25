/**
 * BookNest - Live Search & Autocomplete
 * Debounced search suggestions with keyboard navigation
 */

document.addEventListener('DOMContentLoaded', () => {
  initSearchAutocomplete();
});

function initSearchAutocomplete() {
  const searchInputs = document.querySelectorAll('.nav-search-input');

  searchInputs.forEach(input => {
    const form = input.closest('form');
    if (!form) return;

    // Create dropdown container
    const dropdown = document.createElement('div');
    dropdown.className = 'search-suggestions-dropdown';
    dropdown.style.display = 'none';
    form.style.position = 'relative';
    form.appendChild(dropdown);

    let debounceTimer = null;
    let selectedIndex = -1;

    input.addEventListener('input', (e) => {
      const q = input.value.trim();
      clearTimeout(debounceTimer);

      if (q.length < 2) {
        dropdown.style.display = 'none';
        dropdown.innerHTML = '';
        return;
      }

      debounceTimer = setTimeout(() => {
        fetchSuggestions(q, dropdown, input);
      }, 250);
    });

    // Keyboard navigation
    input.addEventListener('keydown', (e) => {
      const items = dropdown.querySelectorAll('.search-suggest-item');
      if (!items.length || dropdown.style.display === 'none') return;

      if (e.key === 'ArrowDown') {
        e.preventDefault();
        selectedIndex = (selectedIndex + 1) % items.length;
        updateSelection(items, selectedIndex);
      } else if (e.key === 'ArrowUp') {
        e.preventDefault();
        selectedIndex = (selectedIndex - 1 + items.length) % items.length;
        updateSelection(items, selectedIndex);
      } else if (e.key === 'Enter') {
        if (selectedIndex >= 0 && items[selectedIndex]) {
          e.preventDefault();
          window.location.href = items[selectedIndex].getAttribute('href');
        }
      } else if (e.key === 'Escape') {
        dropdown.style.display = 'none';
      }
    });

    // Close on outside click
    document.addEventListener('click', (e) => {
      if (!form.contains(e.target)) {
        dropdown.style.display = 'none';
      }
    });
  });
}

function updateSelection(items, index) {
  items.forEach((item, idx) => {
    if (idx === index) {
      item.classList.add('selected');
      item.scrollIntoView({ block: 'nearest' });
    } else {
      item.classList.remove('selected');
    }
  });
}

function fetchSuggestions(query, dropdown, input) {
  fetch(`api/search-suggest.php?q=${encodeURIComponent(query)}`)
    .then(res => res.json())
    .then(data => {
      if (!data.results || !data.results.length) {
        dropdown.innerHTML = `
          <div style="padding: 16px; text-align: center; color: var(--color-text-muted); font-size: 0.85rem;">
            No books found matching "<strong>${escapeHtml(query)}</strong>"
          </div>
        `;
        dropdown.style.display = 'block';
        return;
      }

      let html = `<div class="search-suggest-header">Matching Books (${data.results.length})</div>`;

      data.results.forEach(book => {
        html += `
          <a href="${book.url}" class="search-suggest-item">
            <img 
              src="${book.cover_image}" 
              alt="${escapeHtml(book.title)}" 
              class="search-suggest-thumb"
              onerror="this.src='https://images.unsplash.com/photo-1544716278-ca5e3f4abd8c?w=100'"
            >
            <div class="search-suggest-info">
              <div class="search-suggest-title">${escapeHtml(book.title)}</div>
              <div class="search-suggest-meta">
                <span>${escapeHtml(book.author)}</span> • <span class="badge-cat">${escapeHtml(book.category)}</span>
              </div>
            </div>
            <div class="search-suggest-price">
              ${book.formatted_price}
            </div>
          </a>
        `;
      });

      html += `
        <a href="books.php?q=${encodeURIComponent(query)}" class="search-suggest-footer">
          View all results for "${escapeHtml(query)}" →
        </a>
      `;

      dropdown.innerHTML = html;
      dropdown.style.display = 'block';
    })
    .catch(err => {
      console.error('Search suggestion error:', err);
    });
}

function escapeHtml(str) {
  const div = document.createElement('div');
  div.textContent = str;
  return div.innerHTML;
}
