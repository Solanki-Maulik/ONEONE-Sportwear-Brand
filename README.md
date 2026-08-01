# STRIDE — Simplified hero, no payment delay, no Site Settings, persistent cart

## Hero
Reverted to a plain, full 100vh black section with the headline/subtext — no
rotating image carousel. Simple and fast.

## Payment
Removed the simulated "processing/approval" delay entirely. Card, UPI, and
Cash on Delivery all place the order immediately on submit, like v11. Address
fields and safe payment-detail storage (never full card numbers/CVV) are
still there.

## Site Settings — removed completely
The admin theme/content editor is gone: `admin/site_settings.php` deleted,
`includes/settings.php` deleted, the `site_settings` table dropped, and every
page reverted to hardcoded values (brand name, ticker text, hero copy, footer
blurb, etc.). To change any of that now, edit the relevant `.php`/`.css`
files directly — that's what "change from code" means in practice:
- Brand name / nav / ticker text -> `includes/nav.php`, `includes/ticker.php`
- Hero headline/subtext -> `index.php`
- Theme colors -> the `:root` block at the top of `style.css`
- Footer blurb -> bottom of each page file

## The cart is now tied to your account, not your browser session
This is the important structural fix: the cart used to live only in the PHP
session, so logging out (which destroys the session) wiped it. It's now
stored in a new `cart_items` database table, keyed to your user account. I
tested this directly — added an item, logged out, logged back in, the item
was still there.

Practical effect: since adding to cart already required being logged in
(this hasn't changed), your cart now follows your account across logins,
browsers, and devices, exactly like a real store.

## The cart bug fix from before, still in place
If a product's stock is 0, Add to Cart shows a clear error instead of
silently doing nothing — and the admin "Stock Quantity" field is required so
it can't be left blank by accident.

## Setup (XAMPP, local)
1. Copy `sports_wear` into `htdocs/`.
2. In phpMyAdmin: click **Databases**, type a name (e.g. `sportswear`) under
   "Create database", click **Create** — this selects it.
3. **Now** click the **Import** tab (should say "Importing into the database
   `sportswear`" near the top) -> choose `stride_sportswear.sql` -> Go.
4. Check `db.php` matches the database name from step 2.
5. Visit `index.php`.

Admin login: `admin@gmail.com` / `admin123`

## Setup (InfinityFree or other shared hosting)
1. Find your existing database name in your hosting control panel.
2. In phpMyAdmin, click that database name first, then Import ->
   `stride_sportswear.sql`.
3. Update `db.php` with your host's real MySQL hostname/username/password —
   never `localhost` on shared hosting. All four values are in your control
   panel's MySQL Databases page.

## If you're patching an existing database instead of reimporting
```sql
DROP TABLE IF EXISTS site_settings;

CREATE TABLE IF NOT EXISTS cart_items (
  id INT NOT NULL AUTO_INCREMENT,
  user_id INT NOT NULL,
  product_id INT NOT NULL,
  size VARCHAR(10) NOT NULL DEFAULT '',
  quantity INT NOT NULL,
  PRIMARY KEY (id),
  UNIQUE KEY user_product_size (user_id, product_id, size),
  CONSTRAINT cart_items_ibfk_1 FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE,
  CONSTRAINT cart_items_ibfk_2 FOREIGN KEY (product_id) REFERENCES products (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```
