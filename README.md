# Simple personal marketplace with Stripe

Demo application using Stripe API in vanilla PHP.

# Deployment

- Install dependencies

```
composer install
```

- Create [Stripe](stripe.com) API keys
- Create a SQL database
- Edit `config.php` accordingly
- Create `items` table

```
CREATE TABLE `items` (
  `id` int(11) NOT NULL,
  `name` varchar(254) NOT NULL,
  `image` varchar(254) DEFAULT NULL,
  `description` text NOT NULL,
  `nb_remaining` int(11) NOT NULL,
  `price` int(11) NOT NULL
);

ALTER TABLE `items`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
```

- Create `sold` table

```
CREATE TABLE `sold` (
`id` int(11) NOT NULL,
`user_email` varchar(254) NOT NULL,
`item_id` int(11) NOT NULL,
`number` int(11) NOT NULL,
`delivered` tinyint(1) DEFAULT NULL
);

ALTER TABLE `sold`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `sold`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
```

- Forbid access to the `admin` directory (with `.htpasswd`)

# Usage

- Add products in `admin`
