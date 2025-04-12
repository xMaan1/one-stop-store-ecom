-- Delete blog menu items from the main menu
DELETE FROM menu_nodes WHERE title = 'Blog' OR reference_type = 'Botble\\Blog\\Models\\Category' OR reference_type = 'Botble\\Blog\\Models\\Tag' OR reference_type = 'Botble\\Blog\\Models\\Post';

-- Delete blog entries from child items
DELETE FROM menu_nodes WHERE parent_id IN (SELECT id FROM menu_nodes WHERE title = 'Blog'); 