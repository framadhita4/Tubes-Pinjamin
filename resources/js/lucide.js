import { createIcons, icons } from 'lucide';

// Caution, this will import all the icons and bundle them.
createIcons({ icons });

// Make createIcons available globally for dynamic content
window.lucide = { createIcons };

// Export for manual icon creation
export { createIcons };
