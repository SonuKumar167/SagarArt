-- Migration: Seed pricing_items with product, category, and pricing data from the provided price sheet
-- This migration populates the pricing table with the values extracted from the image.

START TRANSACTION;

INSERT INTO `pricing_items` (`category`, `item_name`, `slug`, `description`, `unit_label`, `price`, `sort_order`, `is_active`) VALUES
('Handbill', '1/4', 'handbill-1-4', 'Standard handbill size', 'size', 1000.00, 1, 1),
('Handbill', '1/5', 'handbill-1-5', 'Standard handbill size', 'size', 900.00, 2, 1),
('Handbill', '1/6', 'handbill-1-6', 'Standard handbill size', 'size', 800.00, 3, 1),
('Handbill', '1/8', 'handbill-1-8', 'Standard handbill size', 'size', 600.00, 4, 1),
('Handbill', '1/12', 'handbill-1-12', 'Standard handbill size', 'size', 500.00, 5, 1),
('Handbill', '1/16', 'handbill-1-16', 'Standard handbill size', 'size', 450.00, 6, 1),
('Handbill', 'A4', 'handbill-a4', 'Standard A4 size', 'size', 1200.00, 7, 1),
('Handbill', 'A4 Bond', 'handbill-a4-bond', 'A4 Bond paper', 'size', 1800.00, 8, 1),

('Ref. Pad', '1/4', 'ref-pad-1-4', 'Reference pad size', 'size', 1200.00, 1, 1),
('Ref. Pad', '1/5', 'ref-pad-1-5', 'Reference pad size', 'size', 900.00, 2, 1),
('Ref. Pad', '1/6', 'ref-pad-1-6', 'Reference pad size', 'size', 850.00, 3, 1),
('Ref. Pad', '1/8', 'ref-pad-1-8', 'Reference pad size', 'size', 650.00, 4, 1),
('Ref. Pad', '1/12', 'ref-pad-1-12', 'Reference pad size', 'size', 600.00, 5, 1),
('Ref. Pad', '1/16', 'ref-pad-1-16', 'Reference pad size', 'size', 500.00, 6, 1),
('Ref. Pad', 'A4', 'ref-pad-a4', 'Reference pad A4 size', 'size', 1400.00, 7, 1),
('Ref. Pad', 'A4 Bond', 'ref-pad-a4-bond', 'Reference pad A4 Bond paper', 'size', 1900.00, 8, 1),

('Billbook 2 Page Carbon Sada', '1/4', 'billbook-2-page-carbon-sada-1-4', 'Billbook 2 page carbon sada', 'size', 2200.00, 1, 1),
('Billbook 2 Page Carbon Sada', '1/5', 'billbook-2-page-carbon-sada-1-5', 'Billbook 2 page carbon sada', 'size', 1800.00, 2, 1),
('Billbook 2 Page Carbon Sada', '1/6', 'billbook-2-page-carbon-sada-1-6', 'Billbook 2 page carbon sada', 'size', 1600.00, 3, 1),
('Billbook 2 Page Carbon Sada', '1/8', 'billbook-2-page-carbon-sada-1-8', 'Billbook 2 page carbon sada', 'size', 1200.00, 4, 1),
('Billbook 2 Page Carbon Sada', '1/12', 'billbook-2-page-carbon-sada-1-12', 'Billbook 2 page carbon sada', 'size', 1000.00, 5, 1),
('Billbook 2 Page Carbon Sada', '1/16', 'billbook-2-page-carbon-sada-1-16', 'Billbook 2 page carbon sada', 'size', 800.00, 6, 1),
('Billbook 2 Page Carbon Sada', 'A4', 'billbook-2-page-carbon-sada-a4', 'Billbook 2 page carbon sada', 'size', 2300.00, 7, 1),
('Billbook 2 Page Carbon Sada', 'A4 Bond', 'billbook-2-page-carbon-sada-a4-bond', 'Billbook 2 page carbon sada', 'size', 2800.00, 8, 1),

('Billbook 2 Page Carbon Print', '1/4', 'billbook-2-page-carbon-print-1-4', 'Billbook 2 page carbon print', 'size', 2400.00, 1, 1),
('Billbook 2 Page Carbon Print', '1/5', 'billbook-2-page-carbon-print-1-5', 'Billbook 2 page carbon print', 'size', 2000.00, 2, 1),
('Billbook 2 Page Carbon Print', '1/6', 'billbook-2-page-carbon-print-1-6', 'Billbook 2 page carbon print', 'size', 1600.00, 3, 1),
('Billbook 2 Page Carbon Print', '1/8', 'billbook-2-page-carbon-print-1-8', 'Billbook 2 page carbon print', 'size', 1400.00, 4, 1),
('Billbook 2 Page Carbon Print', '1/12', 'billbook-2-page-carbon-print-1-12', 'Billbook 2 page carbon print', 'size', 1100.00, 5, 1),
('Billbook 2 Page Carbon Print', '1/16', 'billbook-2-page-carbon-print-1-16', 'Billbook 2 page carbon print', 'size', 900.00, 6, 1),
('Billbook 2 Page Carbon Print', 'A4', 'billbook-2-page-carbon-print-a4', 'Billbook 2 page carbon print', 'size', 2500.00, 7, 1),
('Billbook 2 Page Carbon Print', 'A4 Bond', 'billbook-2-page-carbon-print-a4-bond', 'Billbook 2 page carbon print', 'size', 2800.00, 8, 1),

('Billbook 3 Page Carbon Print', '1/4', 'billbook-3-page-carbon-print-1-4', 'Billbook 3 page carbon print', 'size', 3400.00, 1, 1),
('Billbook 3 Page Carbon Print', '1/5', 'billbook-3-page-carbon-print-1-5', 'Billbook 3 page carbon print', 'size', 2600.00, 2, 1),
('Billbook 3 Page Carbon Print', '1/6', 'billbook-3-page-carbon-print-1-6', 'Billbook 3 page carbon print', 'size', 2400.00, 3, 1),
('Billbook 3 Page Carbon Print', '1/8', 'billbook-3-page-carbon-print-1-8', 'Billbook 3 page carbon print', 'size', 1400.00, 4, 1),
('Billbook 3 Page Carbon Print', '1/12', 'billbook-3-page-carbon-print-1-12', 'Billbook 3 page carbon print', 'size', 2100.00, 5, 1),
('Billbook 3 Page Carbon Print', '1/16', 'billbook-3-page-carbon-print-1-16', 'Billbook 3 page carbon print', 'size', 1700.00, 6, 1),
('Billbook 3 Page Carbon Print', 'A4', 'billbook-3-page-carbon-print-a4', 'Billbook 3 page carbon print', 'size', 3200.00, 7, 1),
('Billbook 3 Page Carbon Print', 'A4 Bond', 'billbook-3-page-carbon-print-a4-bond', 'Billbook 3 page carbon print', 'size', 3600.00, 8, 1),

('Flex Print', 'Gen Flex', 'flex-print-gen-flex', 'General flex print pricing per square foot (12-15)', 'per sq ft', 12.00, 1, 1),
('Flex Print', '2nd Star Flex', 'flex-print-2nd-star-flex', 'Second star flex print pricing per square foot', 'per sq ft', 18.00, 2, 1),
('Flex Print', 'Star Flex', 'flex-print-star-flex', 'Star flex print pricing per square foot', 'per sq ft', 30.00, 3, 1),
('Flex Print', 'Sticker/Vinyl', 'flex-print-sticker-vinyl', 'Sticker / vinyl print pricing', 'per sq ft', 35.00, 4, 1),
('Flex Print', 'OneWay Vision', 'flex-print-oneway-vision', 'One way vision print pricing', 'per sq ft', 60.00, 5, 1),

('Flex Frame N', 'Gen Flex', 'flex-frame-n-gen-flex', 'Gen Flex normal frame pricing', 'per piece', 35.00, 1, 1),
('Flex Frame N', '2nd Star Flex', 'flex-frame-n-2nd-star-flex', '2nd Star Flex normal frame pricing', 'per piece', 40.00, 2, 1),
('Flex Frame N', 'Star Flex', 'flex-frame-n-star-flex', 'Star Flex normal frame pricing', 'per piece', 45.00, 3, 1),
('Flex Frame N', 'Sticker/Vinyl', 'flex-frame-n-sticker-vinyl', 'Sticker/Vinyl normal frame pricing', 'per piece', 50.00, 4, 1),

('Flex Frame H', 'Gen Flex', 'flex-frame-h-gen-flex', 'Gen Flex high frame pricing', 'per piece', 55.00, 1, 1),
('Flex Frame H', '2nd Star Flex', 'flex-frame-h-2nd-star-flex', '2nd Star Flex high frame pricing', 'per piece', 60.00, 2, 1),
('Flex Frame H', 'Star Flex', 'flex-frame-h-star-flex', 'Star Flex high frame pricing', 'per piece', 65.00, 3, 1),

('Paper Printing', '12x18 Paper (1 Side)', 'paper-printing-12x18-paper-1-side', '12x18 paper single-side print', '1 side', 50.00, 1, 1),
('Paper Printing', '12x18 Paper (2 Side)', 'paper-printing-12x18-paper-2-side', '12x18 paper double-side print', '2 side', 80.00, 2, 1),
('Paper Printing', '12x18 Sticker', 'paper-printing-12x18-sticker', '12x18 sticker print', 'per piece', 60.00, 3, 1),
('Paper Printing', '12x18 Photo Frame', 'paper-printing-12x18-photo-frame', '12x18 photo frame print', 'per piece', 700.00, 4, 1),
('Paper Printing', '100 Visiting Card (1 Side)', 'paper-printing-100-visiting-card-1-side', '100 visiting cards single-side', '1 side', 250.00, 5, 1),
('Paper Printing', '100 Visiting Card (2 Side)', 'paper-printing-100-visiting-card-2-side', '100 visiting cards double-side', '2 side', 260.00, 6, 1),
('Paper Printing', '1000 Visiting Card (1 Side)', 'paper-printing-1000-visiting-card-1-side', '1000 visiting cards single-side', '1 side', 800.00, 7, 1),
('Paper Printing', '1000 Visiting Card (2 Side)', 'paper-printing-1000-visiting-card-2-side', '1000 visiting cards double-side', '2 side', 1200.00, 8, 1),

('Eco Solvent Print', 'Star', 'eco-solvent-print-star', 'Eco solvent print - Star media', 'per sq ft', 50.00, 1, 1),
('Eco Solvent Print', 'Sticker/Vinyl', 'eco-solvent-print-sticker-vinyl', 'Eco solvent print - Sticker / Vinyl media', 'per sq ft', 65.00, 2, 1),
('Eco Solvent Print', 'Photo Paper', 'eco-solvent-print-photo-paper', 'Eco solvent print - Photo Paper media', 'per sq ft', 60.00, 3, 1),
('Eco Solvent Print', 'Translite', 'eco-solvent-print-translite', 'Eco solvent print - Translite media', 'per sq ft', 60.00, 4, 1),
('Eco Solvent Print', 'Transparent', 'eco-solvent-print-transparent', 'Eco solvent print - Transparent media', 'per sq ft', 60.00, 5, 1)
ON DUPLICATE KEY UPDATE
  `category` = VALUES(`category`),
  `item_name` = VALUES(`item_name`),
  `description` = VALUES(`description`),
  `unit_label` = VALUES(`unit_label`),
  `price` = VALUES(`price`),
  `sort_order` = VALUES(`sort_order`),
  `is_active` = VALUES(`is_active`);

COMMIT;
