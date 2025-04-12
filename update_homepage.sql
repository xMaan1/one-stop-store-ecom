USE ecommerce;

UPDATE pages 
SET content = REPLACE(
    REPLACE(
        content, 
        '<div>[featured-brands title="Our Brands"][/featured-brands]</div>', 
        ''
    ), 
    '<div>[featured-news title="Visit Our Blog" description="Our Blog updated the newest trend of the world regularly"][/featured-news]</div>', 
    ''
) 
WHERE id = 1; 