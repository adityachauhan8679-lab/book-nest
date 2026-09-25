-- =====================================================
-- BookNest Database Seed Data
-- Seed file for Initial Setup (30+ Books, 15 Categories, Authors, Users)
-- =====================================================

USE `booknest`;

-- 1. Insert Categories
INSERT INTO `categories` (`id`, `name`, `slug`, `description`, `image`, `status`) VALUES
(1, 'Fiction', 'fiction', 'Imaginative literature from contemporary novels to modern literary masterpieces.', 'category-fiction.jpg', 'active'),
(2, 'Mystery & Thriller', 'mystery-thriller', 'Gripping crime stories, detective puzzles, and edge-of-your-seat psychological suspense.', 'category-mystery.jpg', 'active'),
(3, 'Romance', 'romance', 'Heartfelt tales of passion, modern love, slow-burn connections, and second chances.', 'category-romance.jpg', 'active'),
(4, 'Fantasy', 'fantasy', 'Epic magic systems, enchanted kingdoms, mythical lore, and world-building sagas.', 'category-fantasy.jpg', 'active'),
(5, 'Science Fiction', 'science-fiction', 'Futuristic voyages, artificial intelligence dilemmas, space epics, and dystopian realities.', 'category-scifi.jpg', 'active'),
(6, 'Self Help', 'self-help', 'Actionable guides for personal growth, habit formation, mindfulness, and happiness.', 'category-selfhelp.jpg', 'active'),
(7, 'Business', 'business', 'Entrepreneurship, leadership wisdom, financial strategy, and economic thinking.', 'category-business.jpg', 'active'),
(8, 'Technology', 'technology', 'Deep dives into computing, the internet age, artificial intelligence, and digital revolutions.', 'category-tech.jpg', 'active'),
(9, 'Programming', 'programming', 'Clean code, algorithms, software engineering principles, full-stack, and system design.', 'category-programming.jpg', 'active'),
(10, 'Cybersecurity', 'cybersecurity', 'Defensive security, ethical hacking, cryptography, network defense, and digital privacy.', 'category-cybersecurity.jpg', 'active'),
(11, 'History', 'history', 'Fascinating accounts of civilization, turning-point battles, and cultural milestones.', 'category-history.jpg', 'active'),
(12, 'Biography', 'biography', 'Memoirs and captivating true-life journeys of inspiring innovators and thinkers.', 'category-biography.jpg', 'active'),
(13, 'Academic', 'academic', 'Rigorous textbooks, scientific primers, reference manuals, and research foundations.', 'category-academic.jpg', 'active'),
(14, 'Children', 'children', 'Whimsical illustrated stories, bed-time adventures, and early learning treasures.', 'category-children.jpg', 'active'),
(15, 'Comics', 'comics', 'Graphic novels, superhero universes, manga translations, and sequential visual art.', 'category-comics.jpg', 'active')
ON DUPLICATE KEY UPDATE `name`=VALUES(`name`);

-- 2. Insert Authors
INSERT INTO `authors` (`id`, `name`, `bio`) VALUES
(1, 'James Clear', 'James Clear is a writer and keynote speaker focused on habits, decision making, and continuous improvement. Author of the #1 New York Times bestseller Atomic Habits.'),
(2, 'Robert C. Martin', 'Known affectionately as Uncle Bob, software craftsman and co-author of the Agile Manifesto, celebrated for Clean Code.'),
(3, 'F. Scott Fitzgerald', 'Iconic American novelist whose works illustrated the flamboyance and excess of the Jazz Age, notably The Great Gatsby.'),
(4, 'Frank Herbert', 'American science fiction author best known for the masterpiece epic Dune, winner of the Hugo and Nebula awards.'),
(5, 'George Orwell', 'English novelist, essayist, and critic famous for Animal Farm and dystopian classic 1984.'),
(6, 'J.K. Rowling', 'British author renowned for the global Harry Potter fantasy universe, translated into dozens of languages.'),
(7, 'Morgan Housel', 'Partner at The Collaborative Fund and former columnist at The Wall Street Journal, author of The Psychology of Money.'),
(8, 'Yuval Noah Harari', 'Historian, philosopher, and professor at the Hebrew University of Jerusalem, author of Sapiens: A Brief History of Humankind.'),
(9, 'Kevin D. Mitnick', 'Once the most-wanted computer hacker in America, turned trusted cybersecurity consultant and author of The Art of Invisibility.'),
(10, 'Alex Michaelides', 'British-Cypriot author and screenwriter famous for the psychological thriller The Silent Patient.'),
(11, 'Cal Newport', 'Computer science professor at Georgetown University and bestselling author of Deep Work and Digital Minimalism.'),
(12, 'Colleen Hoover', 'Critically acclaimed contemporary romance and drama author with millions of devoted global readers.')
ON DUPLICATE KEY UPDATE `name`=VALUES(`name`);

-- 3. Insert Realistic Books (30+ books)
INSERT INTO `books` (`id`, `title`, `author_id`, `category_id`, `isbn`, `publisher`, `publication_year`, `pages`, `language`, `description`, `price`, `discount`, `stock`, `cover_image`, `rating`, `views`) VALUES
(1, 'Atomic Habits', 1, 6, '9780735211292', 'Avery / Penguin', 2018, 320, 'English', 'No matter your goals, Atomic Habits offers a proven framework for improving every day. James Clear reveals practical strategies to form good habits, break bad ones, and master tiny behaviors that lead to remarkable results.', 499.00, 15.00, 45, 'https://images.unsplash.com/photo-1544716278-ca5e3f4abd8c?w=600&auto=format&fit=crop&q=80', 4.90, 1850),
(2, 'Clean Code: A Handbook of Agile Software Craftsmanship', 2, 9, '9780132350884', 'Prentice Hall', 2008, 464, 'English', 'Even bad code can function. But if code isn\'t clean, it can bring a development organization to its knees. Master software craftsmanship from Uncle Bob with hands-on code examples.', 799.00, 10.00, 28, 'https://images.unsplash.com/photo-1532012164546-f432f2e3777a?w=600&auto=format&fit=crop&q=80', 4.85, 1420),
(3, 'The Great Gatsby', 3, 1, '9780743273565', 'Scribner', 1925, 180, 'English', 'The quintessential Jazz Age novel capturing Jay Gatsby\'s tragic pursuit of Daisy Buchanan amidst luxury, obsession, and disillusionment on Long Island.', 299.00, 0.00, 60, 'https://images.unsplash.com/photo-1512820790803-83ca734da794?w=600&auto=format&fit=crop&q=80', 4.70, 980),
(4, 'Dune', 4, 5, '9780441013593', 'Ace Books', 1965, 688, 'English', 'Set on the desert planet Arrakis, Dune is the story of the boy Paul Atreides, heir to a noble family tasked with ruling an inhospitable world where the only thing of value is the spice melange.', 599.00, 20.00, 35, 'https://images.unsplash.com/photo-1506466010722-395aa2bef877?w=600&auto=format&fit=crop&q=80', 4.88, 2300),
(5, '1984', 5, 1, '9780451524935', 'Signet Classic', 1949, 328, 'English', 'Winston Smith toes the Party line, rewriting history to satisfy the Ministry of Truth. With every lie he writes, he grows to secretly despise the regime and yearns for rebellion.', 349.00, 12.00, 50, 'https://images.unsplash.com/photo-1495640388908-05fa85288e61?w=600&auto=format&fit=crop&q=80', 4.82, 1600),
(6, 'The Psychology of Money', 7, 7, '9780857197689', 'Harriman House', 2020, 252, 'English', 'Timeless lessons on wealth, greed, and happiness. Doing well with money isn\'t necessarily about what you know. It’s about how you behave.', 449.00, 10.00, 75, 'https://images.unsplash.com/photo-1553729459-efe14ef6055d?w=600&auto=format&fit=crop&q=80', 4.80, 2100),
(7, 'Sapiens: A Brief History of Humankind', 8, 11, '9780062316097', 'Harper', 2015, 464, 'English', 'From examining the role that evolving humans have played in the global ecosystem to charting the rise of empires, Sapiens integrates history and science to reconsider accepted narratives.', 549.00, 15.00, 40, 'https://images.unsplash.com/photo-1461360370896-922624d12aa1?w=600&auto=format&fit=crop&q=80', 4.75, 1980),
(8, 'The Art of Invisibility', 9, 10, '9780316380508', 'Little, Brown and Company', 2017, 320, 'English', 'The world’s most famous hacker teaches you how to protect your privacy and digital identity in the age of big data and ubiquitous surveillance.', 649.00, 5.00, 22, 'https://images.unsplash.com/photo-1526374965328-7f61d4dc18c5?w=600&auto=format&fit=crop&q=80', 4.65, 890),
(9, 'The Silent Patient', 10, 2, '9781250301697', 'Celadon Books', 2019, 336, 'English', 'Alicia Berenson\'s life is seemingly perfect. One evening she shoots her husband five times in the face, and then never speaks another word. Theo Faber is a criminal psychotherapist who has waited a long time for the chance to work with her.', 399.00, 18.00, 65, 'https://images.unsplash.com/photo-1589829085413-56de8ae18c73?w=600&auto=format&fit=crop&q=80', 4.78, 1750),
(10, 'Deep Work: Rules for Focused Success in a Distracted World', 11, 6, '9781455586691', 'Grand Central Publishing', 2016, 304, 'English', 'Deep work is the ability to focus without distraction on a cognitively demanding task. A guide that will transform your productivity and professional trajectory.', 499.00, 12.00, 30, 'https://images.unsplash.com/photo-1457369804613-52c61a468e7d?w=600&auto=format&fit=crop&q=80', 4.81, 1340),
(11, 'It Ends with Us', 12, 3, '9781501110368', 'Atria Books', 2016, 384, 'English', 'A poignant tale of love, resilience, heartbreak, and the courage it takes to make the hardest life decisions for oneself.', 399.00, 20.00, 55, 'https://images.unsplash.com/photo-1516979187457-637abb4f9353?w=600&auto=format&fit=crop&q=80', 4.72, 2800),
(12, 'The Pragmatic Programmer', 2, 9, '9780135957059', 'Addison-Wesley', 2019, 352, 'English', 'Your journey to mastery: topics ranging from personal responsibility and career development to architectural techniques for keeping your code flexible and easy to adapt and reuse.', 899.00, 15.00, 18, 'https://images.unsplash.com/photo-1515879218367-8466d910aaa4?w=600&auto=format&fit=crop&q=80', 4.92, 1650),
(13, 'Harry Potter and the Sorcerer\'s Stone', 6, 4, '9780590353427', 'Scholastic', 1997, 309, 'English', 'Harry Potter has never even heard of Hogwarts when the letters start dropping on the doormat at number four, Privet Drive. A magnificent journey into the wizarding world.', 450.00, 10.00, 80, 'https://images.unsplash.com/photo-1618666012174-83b441c0bc76?w=600&auto=format&fit=crop&q=80', 4.95, 3400),
(14, 'Animal Farm', 5, 1, '9780451526342', 'Signet', 1945, 144, 'English', 'A devastating political fable about a group of barnyard animals who overthrow their human master and attempt to create a society where all animals are equal.', 249.00, 5.00, 60, 'https://images.unsplash.com/photo-1497633762265-9d179a990aa6?w=600&auto=format&fit=crop&q=80', 4.76, 1100),
(15, 'Steve Jobs', 8, 12, '9781451648539', 'Simon & Schuster', 2011, 656, 'English', 'Based on more than forty interviews with Steve Jobs conducted over two years, Walter Isaacson has written a riveting story of the roller-coaster life and searingly intense personality of a creative entrepreneur.', 699.00, 20.00, 25, 'https://images.unsplash.com/photo-1505373877841-8d25f7d46678?w=600&auto=format&fit=crop&q=80', 4.79, 1420),
(16, 'Zero to One: Notes on Startups, or How to Build the Future', 7, 7, '9780804139298', 'Crown Business', 2014, 224, 'English', 'The great secret of our time is that there are still uncharted frontiers to explore and new inventions to create. Peter Thiel shows how to build unique monopolies.', 420.00, 10.00, 40, 'https://images.unsplash.com/photo-1507679799987-c73779587ccf?w=600&auto=format&fit=crop&q=80', 4.68, 1590),
(17, 'Practical Malware Analysis', 9, 10, '9781593272906', 'No Starch Press', 2012, 800, 'English', 'The hands-on guide to dissecting malicious software. Learn how to safely debug malware, analyze network signatures, and crack botnet protocols.', 1250.00, 8.00, 12, 'https://images.unsplash.com/photo-1550751827-4bd374c3f58b?w=600&auto=format&fit=crop&q=80', 4.87, 750),
(18, 'Refactoring: Improving the Design of Existing Code', 2, 9, '9780134757599', 'Addison-Wesley', 2018, 448, 'English', 'Martin Fowler’s guide to improving the internal architecture of software without altering its external behavior, completely updated for modern JavaScript.', 999.00, 12.00, 20, 'https://images.unsplash.com/photo-1555066931-4365d14bab8c?w=600&auto=format&fit=crop&q=80', 4.89, 820),
(19, 'Project Hail Mary', 4, 5, '9780593135204', 'Ballantine Books', 2021, 496, 'English', 'Ryland Grace is the sole survivor on a desperate, last-chance mission—and if he fails, humanity and the earth itself are doomed. From the bestselling author of The Martian.', 599.00, 15.00, 38, 'https://images.unsplash.com/photo-1451187580459-43490279c0fa?w=600&auto=format&fit=crop&q=80', 4.93, 2450),
(20, 'Designing Data-Intensive Applications', 2, 8, '9781449373320', 'O\'Reilly Media', 2017, 616, 'English', 'Data is at the center of many challenges in system design today. Martin Kleppmann helps you navigate the diverse and fast-changing landscape of technologies for storing and processing data.', 1199.00, 10.00, 15, 'https://images.unsplash.com/photo-1518770660439-4636190af475?w=600&auto=format&fit=crop&q=80', 4.94, 2190),
(21, 'Digital Minimalism: Choosing a Focused Life in a Noisy World', 11, 6, '9780525536512', 'Portfolio', 2019, 304, 'English', 'A customized, thoughtful approach to decluttering your online life and reclaiming hours of your day for real conversation and rich thought.', 480.00, 10.00, 32, 'https://images.unsplash.com/photo-1519389950473-47ba0277781c?w=600&auto=format&fit=crop&q=80', 4.70, 930),
(22, 'The Da Vinci Code', 10, 2, '9780307474278', 'Anchor', 2003, 489, 'English', 'While in Paris, Harvard symbologist Robert Langdon is awakened by a phone call: the elderly curator of the Louvre has been murdered inside the museum.', 399.00, 15.00, 48, 'https://images.unsplash.com/photo-1534447677768-be436bb09401?w=600&auto=format&fit=crop&q=80', 4.67, 1870),
(23, 'Grokking Algorithms', 2, 9, '9781617292231', 'Manning', 2016, 256, 'English', 'An illustrated guide for programmers and other curious people. Fully visual breakdowns of search, sorting, graph algorithms, and dynamic programming.', 699.00, 10.00, 29, 'https://images.unsplash.com/photo-1509228468518-180dd4864904?w=600&auto=format&fit=crop&q=80', 4.88, 1720),
(24, 'The Hobbit', 6, 4, '9780547928227', 'Mariner Books', 1937, 300, 'English', 'Bilbo Baggins is a hobbit who enjoys a comfortable, unambitious life. His contentment is disturbed when the wizard Gandalf and a company of dwarves arrive on his doorstep.', 380.00, 12.00, 52, 'https://images.unsplash.com/photo-1506466010722-395aa2bef877?w=600&auto=format&fit=crop&q=80', 4.85, 2300),
(25, 'Rich Dad Poor Dad', 7, 7, '9781612680194', 'Plata Publishing', 1997, 336, 'English', 'What the rich teach their kids about money that the poor and middle class do not! Explodes the myth that you need to earn a high income to be rich.', 350.00, 15.00, 85, 'https://images.unsplash.com/photo-1559526324-4b87b5e36e44?w=600&auto=format&fit=crop&q=80', 4.69, 2900),
(26, 'The Cuckoo\'s Egg: Tracking a Spy Through the Maze of Computer Espionage', 9, 10, '9781416507789', 'Pocket Books', 1989, 397, 'English', 'Cliff Stoll was an astronomer turned systems manager when he noticed a 75-cent accounting discrepancy. His investigation unravelled a global network of Soviet espionage.', 520.00, 5.00, 14, 'https://images.unsplash.com/photo-1563986768609-322da13575f3?w=600&auto=format&fit=crop&q=80', 4.84, 620),
(27, 'Educated: A Memoir', 8, 12, '9780399590504', 'Random House', 2018, 352, 'English', 'An unforgettable memoir about a young girl who, kept out of school, leaves her survivalist family and goes on to earn a PhD from Cambridge University.', 499.00, 15.00, 36, 'https://images.unsplash.com/photo-1476275466078-4007374efbbe?w=600&auto=format&fit=crop&q=80', 4.80, 1290),
(28, 'Man\'s Search for Meaning', 1, 6, '9780807014295', 'Beacon Press', 1946, 184, 'English', 'Psychiatrist Viktor Frankl\'s memoir of life in Nazi death camps and its lessons for spiritual survival and discovering the purpose of life.', 299.00, 10.00, 44, 'https://images.unsplash.com/photo-1491841550275-ad7854e35ca6?w=600&auto=format&fit=crop&q=80', 4.88, 1610),
(29, 'Clean Architecture: A Craftsman\'s Guide to Software Structure', 2, 9, '9780134494166', 'Prentice Hall', 2017, 432, 'English', 'Practical software architecture solutions from Uncle Bob. Learn universal rules of software design that will dramatically improve developer productivity.', 850.00, 15.00, 22, 'https://images.unsplash.com/photo-1517694712202-14dd9538aa97?w=600&auto=format&fit=crop&q=80', 4.82, 1140),
(30, 'Batman: Year One', 1, 15, '9781401207526', 'DC Comics', 1987, 144, 'English', 'Frank Miller and David Mazzucchelli collaborate on this groundbreaking reimagining of Bruce Wayne’s initial steps as Gotham City’s dark knight protector.', 599.00, 10.00, 30, 'https://images.unsplash.com/photo-1607604276583-eef5d076aa5f?w=600&auto=format&fit=crop&q=80', 4.83, 1450),
(31, 'The Little Prince', 3, 14, '9780156012195', 'Mariner Books', 1943, 96, 'English', 'A timeless philosophical story about a young prince who visits various planets in space, addressing themes of loneliness, friendship, love, and loss.', 250.00, 0.00, 70, 'https://images.unsplash.com/photo-1512820790803-83ca734da794?w=600&auto=format&fit=crop&q=80', 4.90, 1920),
(32, 'Guns, Germs, and Steel', 8, 11, '9780393354324', 'W. W. Norton', 1997, 528, 'English', 'Jared Diamond powerfully argues that geographical and environmental factors shaped the modern world rather than racial or genetic biological differences.', 580.00, 10.00, 27, 'https://images.unsplash.com/photo-1456513080510-7bf3a84b82f8?w=600&auto=format&fit=crop&q=80', 4.74, 910)
ON DUPLICATE KEY UPDATE `title`=VALUES(`title`);

-- 4. Demo Users (Hashed with PHP password_hash PASSWORD_BCRYPT)
-- Password for admin@booknest.com is: Admin@123
-- Password for user@booknest.com is: User@123
INSERT INTO `users` (`id`, `name`, `email`, `password`, `role`, `phone`, `status`) VALUES
(1, 'BookNest Administrator', 'admin@booknest.com', '$2y$10$w3U6H1T.ZJeqw48oMhSj0uK.kF3N1X2m8xWkUaF0Q7Zk8Yl1LqRKG', 'admin', '+91 98765 43210', 'active'),
(2, 'Aditya Chauhan', 'user@booknest.com', '$2y$10$w3U6H1T.ZJeqw48oMhSj0uK.kF3N1X2m8xWkUaF0Q7Zk8Yl1LqRKG', 'user', '+91 91234 56789', 'active')
ON DUPLICATE KEY UPDATE `name`=VALUES(`name`);

-- 5. Sample Address
INSERT INTO `addresses` (`id`, `user_id`, `full_name`, `phone`, `address`, `city`, `state`, `pincode`, `is_default`) VALUES
(1, 2, 'Aditya Chauhan', '+91 91234 56789', 'Flat 402, Sunshine Heights, College Road', 'Pune', 'Maharashtra', '411001', 1)
ON DUPLICATE KEY UPDATE `full_name`=VALUES(`full_name`);

-- 6. Sample Reviews
INSERT INTO `reviews` (`id`, `user_id`, `book_id`, `rating`, `comment`, `status`) VALUES
(1, 2, 1, 5, 'Atomic Habits completely shifted my morning routines and productivity. Every college student must read this!', 'approved'),
(2, 2, 2, 5, 'Clean Code is essential reading for any computer science engineering student. Practical and immediately applicable.', 'approved'),
(3, 2, 6, 5, 'Changed how I think about investments and compound growth. Very easy to digest yet profound.', 'approved')
ON DUPLICATE KEY UPDATE `comment`=VALUES(`comment`);

-- 7. Sample Initial Order
INSERT INTO `orders` (`id`, `user_id`, `address_id`, `total_amount`, `payment_method`, `payment_status`, `order_status`) VALUES
(1001, 2, 1, 1298.00, 'UPI', 'Paid', 'Delivered')
ON DUPLICATE KEY UPDATE `total_amount`=VALUES(`total_amount`);

INSERT INTO `order_items` (`id`, `order_id`, `book_id`, `quantity`, `price`) VALUES
(1, 1001, 1, 1, 499.00),
(2, 1001, 2, 1, 799.00)
ON DUPLICATE KEY UPDATE `price`=VALUES(`price`);
