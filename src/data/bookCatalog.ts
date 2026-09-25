import { Book } from '../types';

export interface CategoryInfo {
  name: string;
  slug: string;
  icon: string;
  color: string;
  accentBg: string;
  count: number;
  description: string;
}

export const CATEGORIES: CategoryInfo[] = [
  {
    name: 'All Categories',
    slug: 'all',
    icon: 'fa-solid fa-shapes',
    color: 'text-amber-600',
    accentBg: 'bg-amber-50',
    count: 12,
    description: 'Explore the complete BookNest collection'
  },
  {
    name: 'Fiction & Literature',
    slug: 'fiction',
    icon: 'fa-solid fa-feather-pointed',
    color: 'text-rose-600',
    accentBg: 'bg-rose-50',
    count: 3,
    description: 'Timeless classics, evocative prose & modern narrative masterworks'
  },
  {
    name: 'Computer Science & Tech',
    slug: 'programming',
    icon: 'fa-solid fa-laptop-code',
    color: 'text-indigo-600',
    accentBg: 'bg-indigo-50',
    count: 3,
    description: 'Engineering craftsmanship, system design & security'
  },
  {
    name: 'Science Fiction',
    slug: 'science-fiction',
    icon: 'fa-solid fa-rocket',
    color: 'text-purple-600',
    accentBg: 'bg-purple-50',
    count: 2,
    description: 'Cosmic worldbuilding, speculative futures & legendary space sagas'
  },
  {
    name: 'Business & Economics',
    slug: 'business',
    icon: 'fa-solid fa-chart-line',
    color: 'text-emerald-600',
    accentBg: 'bg-emerald-50',
    count: 2,
    description: 'Behavioral finance, market psychology & executive leadership'
  },
  {
    name: 'Self Help & Mindset',
    slug: 'self-help',
    icon: 'fa-solid fa-brain',
    color: 'text-amber-600',
    accentBg: 'bg-amber-50',
    count: 2,
    description: 'Habit mastery, cognitive resilience & continuous self-growth'
  }
];

export const INITIAL_BOOKS: Book[] = [
  {
    id: 1,
    title: 'Atomic Habits',
    author: 'James Clear',
    category: 'Self Help',
    categorySlug: 'self-help',
    price: 499,
    discount: 15,
    rating: 4.9,
    stock: 45,
    isbn: '9780735211292',
    publisher: 'Avery / Penguin Random House',
    pages: 320,
    year: 2018,
    language: 'English',
    coverImage: 'https://images.unsplash.com/photo-1544716278-ca5e3f4abd8c?w=600&auto=format&fit=crop&q=80',
    description: 'An easy and proven way to build good habits and break bad ones. Practical strategies for continuous self-improvement through tiny 1% daily adjustments.'
  },
  {
    id: 2,
    title: 'Clean Code: A Handbook of Agile Software Craftsmanship',
    author: 'Robert C. Martin',
    category: 'Programming',
    categorySlug: 'programming',
    price: 799,
    discount: 10,
    rating: 4.85,
    stock: 28,
    isbn: '9780132350884',
    publisher: 'Prentice Hall',
    pages: 464,
    year: 2008,
    language: 'English',
    coverImage: 'https://images.unsplash.com/photo-1532012164546-f432f2e3777a?w=600&auto=format&fit=crop&q=80',
    description: 'Even bad code can function. But if code isn\'t clean, it can bring a development organization to its knees. Master professional craftsmanship and refactoring.'
  },
  {
    id: 3,
    title: 'The Great Gatsby',
    author: 'F. Scott Fitzgerald',
    category: 'Fiction',
    categorySlug: 'fiction',
    price: 299,
    discount: 0,
    rating: 4.7,
    stock: 60,
    isbn: '9780743273565',
    publisher: 'Scribner',
    pages: 180,
    year: 1925,
    language: 'English',
    coverImage: 'https://images.unsplash.com/photo-1512820790803-83ca734da794?w=600&auto=format&fit=crop&q=80',
    description: 'The quintessential Jazz Age novel capturing Jay Gatsby\'s tragic pursuit of Daisy Buchanan amidst luxury, obsession, and American disillusionment.'
  },
  {
    id: 4,
    title: 'Dune: The Graphic & Epic Chronicle',
    author: 'Frank Herbert',
    category: 'Science Fiction',
    categorySlug: 'science-fiction',
    price: 599,
    discount: 20,
    rating: 4.88,
    stock: 35,
    isbn: '9780441013593',
    publisher: 'Ace Books',
    pages: 688,
    year: 1965,
    language: 'English',
    coverImage: 'https://images.unsplash.com/photo-1506466010722-395aa2bef877?w=600&auto=format&fit=crop&q=80',
    description: 'Set on the desert planet Arrakis, Dune is the story of Paul Atreides, heir to a noble family in a galactic empire controlled by the priceless spice melange.'
  },
  {
    id: 5,
    title: '1984: Definitive Edition',
    author: 'George Orwell',
    category: 'Fiction',
    categorySlug: 'fiction',
    price: 349,
    discount: 12,
    rating: 4.82,
    stock: 50,
    isbn: '9780451524935',
    publisher: 'Signet Classic',
    pages: 328,
    year: 1949,
    language: 'English',
    coverImage: 'https://images.unsplash.com/photo-1495640388908-05fa85288e61?w=600&auto=format&fit=crop&q=80',
    description: 'Winston Smith toes the Party line, rewriting history to satisfy the Ministry of Truth until rebellion stirs within his thoughts under Big Brother\'s gaze.'
  },
  {
    id: 6,
    title: 'The Psychology of Money',
    author: 'Morgan Housel',
    category: 'Business',
    categorySlug: 'business',
    price: 449,
    discount: 10,
    rating: 4.8,
    stock: 75,
    isbn: '9780857197689',
    publisher: 'Harriman House',
    pages: 256,
    year: 2020,
    language: 'English',
    coverImage: 'https://images.unsplash.com/photo-1553729459-efe14ef6055d?w=600&auto=format&fit=crop&q=80',
    description: 'Timeless lessons on wealth, greed, and happiness. Doing well with money isn\'t necessarily about what you know. It\'s about how you behave.'
  },
  {
    id: 7,
    title: 'Design Patterns: Elements of Reusable Object-Oriented Software',
    author: 'Erich Gamma, Richard Helm, Ralph Johnson, John Vlissides',
    category: 'Programming',
    categorySlug: 'programming',
    price: 899,
    discount: 15,
    rating: 4.9,
    stock: 14,
    isbn: '9780201633610',
    publisher: 'Addison-Wesley Professional',
    pages: 416,
    year: 1994,
    language: 'English',
    coverImage: 'https://images.unsplash.com/photo-1517694712202-14dd9538aa97?w=600&auto=format&fit=crop&q=80',
    description: 'Capturing a wealth of experience about the design of object-oriented software, four top-notch designers present a catalog of simple and succinct solutions.'
  },
  {
    id: 8,
    title: 'The Art of Invisibility',
    author: 'Kevin D. Mitnick',
    category: 'Programming',
    categorySlug: 'programming',
    price: 649,
    discount: 5,
    rating: 4.65,
    stock: 5, // low stock for alert testing
    isbn: '9780316380508',
    publisher: 'Little, Brown and Company',
    pages: 320,
    year: 2017,
    language: 'English',
    coverImage: 'https://images.unsplash.com/photo-1526374965328-7f61d4dc18c5?w=600&auto=format&fit=crop&q=80',
    description: 'The world\'s most famous hacker teaches you how to be safe in the age of big data and surveillance. Master digital privacy.'
  },
  {
    id: 9,
    title: 'Thinking, Fast and Slow',
    author: 'Daniel Kahneman',
    category: 'Self Help',
    categorySlug: 'self-help',
    price: 520,
    discount: 10,
    rating: 4.78,
    stock: 32,
    isbn: '9780374533557',
    publisher: 'Farrar, Straus and Giroux',
    pages: 499,
    year: 2011,
    language: 'English',
    coverImage: 'https://images.unsplash.com/photo-1457369804613-52c61a468e7d?w=600&auto=format&fit=crop&q=80',
    description: 'Nobel laureate Daniel Kahneman explores the two systems that drive the way we think: System 1 is fast, intuitive, and emotional; System 2 is slower and more logical.'
  },
  {
    id: 10,
    title: 'Foundation',
    author: 'Isaac Asimov',
    category: 'Science Fiction',
    categorySlug: 'science-fiction',
    price: 420,
    discount: 8,
    rating: 4.81,
    stock: 3, // very low stock
    isbn: '9780553293357',
    publisher: 'Spectra / Bantam Books',
    pages: 255,
    year: 1951,
    language: 'English',
    coverImage: 'https://images.unsplash.com/photo-1451187580459-43490279c0fa?w=600&auto=format&fit=crop&q=80',
    description: 'Hari Seldon uses mathematical psychohistory to predict the fall of the Galactic Empire and establishes the Foundation to preserve human knowledge.'
  },
  {
    id: 11,
    title: 'To Kill a Mockingbird',
    author: 'Harper Lee',
    category: 'Fiction',
    categorySlug: 'fiction',
    price: 380,
    discount: 0,
    rating: 4.92,
    stock: 42,
    isbn: '9780060935467',
    publisher: 'Harper Perennial Modern Classics',
    pages: 336,
    year: 1960,
    language: 'English',
    coverImage: 'https://images.unsplash.com/photo-1476275466078-4007374efbbe?w=600&auto=format&fit=crop&q=80',
    description: 'Compassionate, dramatic, and deeply moving, Harper Lee\'s Pulitzer Prize-winning masterpiece explores racism and legal justice in Maycomb, Alabama.'
  },
  {
    id: 12,
    title: 'Zero to One: Notes on Startups',
    author: 'Peter Thiel, Blake Masters',
    category: 'Business',
    categorySlug: 'business',
    price: 499,
    discount: 14,
    rating: 4.74,
    stock: 24,
    isbn: '9780804139298',
    publisher: 'Crown Business',
    pages: 224,
    year: 2014,
    language: 'English',
    coverImage: 'https://images.unsplash.com/photo-1486406146926-c627a92ad1ab?w=600&auto=format&fit=crop&q=80',
    description: 'The great secret of our time is that there are still uncharted frontiers to explore. How to build companies that create new things from zero to one.'
  }
];
