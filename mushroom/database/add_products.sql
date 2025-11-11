-- Add 5 Real Weikfield Mushroom Products

-- Product 1: Button Mushrooms
INSERT INTO products (title, slug, description, short_description, price, discount_price, category_id, features, is_featured, is_active, views, downloads) VALUES
('Weikfield Button Mushrooms - Fresh Pack (200g)', 
'weikfield-button-mushrooms-fresh-200g',
'Premium quality fresh button mushrooms, carefully handpicked and packed to ensure maximum freshness. Our button mushrooms are grown in controlled environment farms using the latest agricultural technology. Perfect for salads, pizzas, pasta, and various Indian and continental dishes. Rich in protein, vitamins, and minerals, these mushrooms are a healthy addition to your daily diet.',
'Fresh, premium quality button mushrooms - 200g pack',
89.00,
79.00,
1,
'100% Fresh and Natural
No Preservatives or Chemicals
Rich in Protein and Vitamins
Carefully Handpicked
Perfect for All Cuisines
Hygienically Packed
Farm Fresh Quality
Low in Calories',
1,
1,
245,
89);

-- Product 2: Oyster Mushrooms
INSERT INTO products (title, slug, description, short_description, price, discount_price, category_id, features, is_featured, is_active, views, downloads) VALUES
('Weikfield Oyster Mushrooms - Premium Quality (250g)',
'weikfield-oyster-mushrooms-premium-250g',
'Experience the delicate flavor and unique texture of our premium oyster mushrooms. Grown in state-of-the-art facilities, these mushrooms are known for their mild, slightly sweet taste and velvety texture. Oyster mushrooms are packed with nutrients including antioxidants, vitamins B and D, and essential minerals. Ideal for stir-fries, soups, and grilled preparations.',
'Premium oyster mushrooms with delicate flavor - 250g',
129.00,
115.00,
1,
'Premium Quality Oyster Mushrooms
Rich in Antioxidants
High Vitamin B & D Content
Delicate Sweet Flavor
Perfect for Stir-fries
Velvety Smooth Texture
Organically Grown
Freshness Guaranteed',
1,
1,
198,
67);

-- Product 3: Dried Shiitake Mushrooms
INSERT INTO products (title, slug, description, short_description, price, discount_price, category_id, features, is_featured, is_active, views, downloads) VALUES
('Weikfield Dried Shiitake Mushrooms - Premium Grade (100g)',
'weikfield-dried-shiitake-mushrooms-100g',
'Premium grade dried shiitake mushrooms, carefully selected and processed to retain maximum flavor and nutritional value. Known for their rich, umami taste and meaty texture, shiitake mushrooms are a staple in Asian cuisine. These dried mushrooms have a longer shelf life and can be easily rehydrated for use in soups, broths, stir-fries, and medicinal preparations. Rich in vitamins, minerals, and immune-boosting compounds.',
'Premium dried shiitake mushrooms - 100g pack',
299.00,
269.00,
2,
'Premium Grade Shiitake
Rich Umami Flavor
Long Shelf Life
Easy to Rehydrate
Immune Boosting Properties
High in Vitamin D
Meaty Texture
Authentic Asian Flavor',
1,
1,
312,
124);

-- Product 4: Mushroom Growing Kit
INSERT INTO products (title, slug, description, short_description, price, discount_price, category_id, features, is_featured, is_active, views, downloads) VALUES
('Weikfield DIY Mushroom Growing Kit - Oyster Variety',
'weikfield-diy-mushroom-growing-kit-oyster',
'Grow your own fresh mushrooms at home with our easy-to-use DIY mushroom growing kit! This complete kit includes everything you need to cultivate fresh oyster mushrooms in the comfort of your home. Perfect for beginners, families, and mushroom enthusiasts. The kit comes with pre-colonized substrate, detailed instructions, and support materials. Watch your mushrooms grow in just 7-10 days! A fun, educational, and rewarding experience that provides fresh, organic mushrooms for your kitchen.',
'Complete DIY kit to grow fresh oyster mushrooms at home',
599.00,
549.00,
4,
'Complete Growing Kit
Ready to Use Substrate
Detailed Instructions Included
Harvest in 7-10 Days
Multiple Flushes Possible
Educational & Fun
Perfect for Beginners
Organic & Chemical-Free',
1,
1,
456,
178);

-- Product 5: Mushroom Extract Powder
INSERT INTO products (title, slug, description, short_description, price, discount_price, category_id, features, is_featured, is_active, views, downloads) VALUES
('Weikfield Mushroom Extract Powder - Immunity Booster (50g)',
'weikfield-mushroom-extract-powder-immunity-50g',
'Premium quality mushroom extract powder made from a blend of medicinal mushrooms including Reishi, Cordyceps, and Lion\'s Mane. This concentrated powder is rich in beta-glucans, antioxidants, and bioactive compounds known for their immune-boosting and health-promoting properties. Easy to incorporate into your daily routine - add to smoothies, coffee, tea, or warm water. Supports immunity, cognitive function, energy levels, and overall wellness. 100% natural with no artificial additives.',
'Premium mushroom extract powder for immunity - 50g',
449.00,
399.00,
3,
'Blend of Medicinal Mushrooms
Rich in Beta-Glucans
Immunity Booster
Supports Cognitive Function
100% Natural Extract
No Artificial Additives
Easy to Use
Concentrated Formula',
1,
1,
523,
201);

-- Update category product counts
UPDATE categories SET updated_at = NOW() WHERE id IN (1, 2, 3, 4);
