# M4P Quick Order for PrestaShop 8 & 9

**Put your best sellers on the home page with a quantity field next to each one, so a returning customer fills the cart in one screen.**

> **Meta description (149 chars):** Add a quick order form to your PrestaShop home page: selected products, quantity fields and one button to the cart. Free MIT module for B2B shops.

---

## Why quick ordering matters in wholesale

A wholesale customer does not browse. They know what they buy, they buy it every month, and every
extra click costs them time:

- **Repeat orders take minutes, not a shopping trip** — no category pages, no product pages, no
  going back and forth to the cart
- **Larger baskets** — with the whole range in one view, adding one more line is easy
- **Fewer orders placed by phone and e-mail** — the ones your sales team has to retype by hand
- **Works on a phone** — the form is a plain HTML table of products and quantity fields

## What the module does

The module adds a block to the home page with the products you pick in the back office. Each product
shows its image, name, price, stock and a quantity field, plus a combination selector when the
product has combinations. One button sends everything to the cart and takes the customer to checkout.

### Key features

- **You choose the products** — a multi-select in the back office, no automatic guessing
- **Combinations supported** — the customer picks a combination and its price and stock are shown
- **Stock aware** — products and combinations with almost no stock are skipped, so nobody orders
  what you cannot ship
- **One switch to turn it off** — the block disappears without uninstalling anything
- **Works with hidden prices** — when `m4ploginaccess` hides prices from guests, the form shows a
  "please log in" message instead of the order button

### What guests see

With prices hidden from guests, the block still appears but the ordering controls are replaced by a
message asking the visitor to log in. Without that module, the form behaves like the rest of the
shop.

## Compatibility

| | |
|---|---|
| PrestaShop | 1.7.6 – 9.x (tested on 9.1.3) |
| PHP | 7.2.5+ |
| Requirements | none; optional integration with `m4ploginaccess` |
| Multistore | Configuration is shared across shops |
| Themes | Needs a theme that renders the `displayHome` hook (all standard themes do) |

The module performs no core overrides and adds no database tables.

## Installation

1. Upload and install the module from **Modules → Module Manager**.
2. Open the module configuration page.
3. Pick the products that should appear in the form and save.
4. Open the home page — the block appears below the other home sections.

If the block does not show up, check that the products you picked are active and have stock; the
module skips products it cannot sell.

## Configuration options

| Setting | Description |
|---|---|
| **Enable Quick Order** | Shows or hides the whole block without uninstalling the module. |
| **Select Products** | The products listed in the form, in the order returned by the catalogue. Pick the ones customers reorder most often. |

## Frequently asked questions

**How many products can I put in the form?**
Technically as many as you like, but the form is meant for a short list of repeat purchases — a
dozen products keeps it readable on a phone.

**What happens when someone orders more than you have in stock?**
The cart refuses the line and the customer gets a message explaining that the quantity exceeds
available stock. Nothing is added silently.

**Does it work with product combinations?**
Yes. Products with combinations get a selector, and price and stock follow the chosen combination.

**Can I show the form somewhere else than the home page?**
Not from the back office — the module hooks into `displayHome`. Another position needs a small code
change, which is easy in a fork.

---

**Keywords:** PrestaShop quick order, B2B order form, wholesale reorder, bulk order PrestaShop,
quantity form, repeat orders, home page order form.

## License

MIT — see [LICENSE](LICENSE). Free to use commercially, fork and modify; keep the copyright notice.

## Contributing

Bug reports and pull requests are welcome — see [CONTRIBUTING.md](CONTRIBUTING.md). For security
issues, follow [SECURITY.md](SECURITY.md) instead of opening a public issue.

---

Built by [Nice Code](https://nice-code.com/pl/produkty/sklep-b2b-prestashop) — we build B2B stores on PrestaShop.

© Nice Code sp. z o.o. (Modules4Presta) — released under the MIT license.
