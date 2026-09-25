// Defensive guard for window.fetch in iframe / sandbox environments
try {
  let _rawFetch = window.fetch;
  let _fetch = function (...args: Parameters<typeof fetch>) {
    return _rawFetch.apply(window, args);
  };
  Object.defineProperty(window, 'fetch', {
    get() {
      return _fetch;
    },
    set(val) {
      _fetch = val;
    },
    configurable: true,
    enumerable: true,
  });
} catch (e) {
  // Silent fallback
}

import {StrictMode} from 'react';
import {createRoot} from 'react-dom/client';
import App from './App.tsx';
import './index.css';

createRoot(document.getElementById('root')!).render(
  <StrictMode>
    <App />
  </StrictMode>,
);
