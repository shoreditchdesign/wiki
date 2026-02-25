# OpalBPM — CMS Collection Fields

---

## 1. Client Logos
- **Slug:** `client-logos`

| Field | Type | Required | Editable | Help Text / Validations |
|---|---|---|---|---|
| Logo image | Image | No | Yes | — |
| Name | PlainText | Yes | Yes | Max 256 chars |
| Slug | PlainText | Yes | Yes | Must be alphanumerical, no spaces or special characters. Max 256 chars |

---

## 2. Testimionials *(note: typo in collection name)*
- **Slug:** `testimionials`

| Field | Type | Required | Editable | Help Text / Validations |
|---|---|---|---|---|
| Testimonial | PlainText (multi-line) | No | Yes | "Enter quote as long text content" |
| Role | PlainText (single-line) | No | Yes | "Enter role of author in company" |
| Company | PlainText (single-line) | No | Yes | "Enter company name" |
| Image | Image | No | Yes | "Enter splash image" |
| Order of appearance | Number (decimal) | No | Yes | "Optional index to rank items by priority". No negatives, 1 decimal precision |
| Name | PlainText | Yes | Yes | Max 256 chars |
| Slug | PlainText | Yes | Yes | Must be alphanumerical, no spaces or special characters. Max 256 chars |

---

## 3. ↳ Products
- **Slug:** `products`

| Field | Type | Required | Editable | Help Text / Validations |
|---|---|---|---|---|
| Icon | Image | No | Yes | — |
| Order of Appearance | Number (decimal) | No | Yes | "Optional index to rank items by priority in the navigation dropdown". No negatives, 1 decimal precision |
| Name | PlainText | Yes | Yes | "As displayed in the navigation bar." Max 256 chars |
| Slug | PlainText | Yes | Yes | Must be alphanumerical, no spaces or special characters. Max 256 chars |

---

## 4. Cases
- **Slug:** `casestudy`

| Field | Type | Required | Editable | Help Text / Validations |
|---|---|---|---|---|
| Thumbnail / Image | Image | No | Yes | "Featured in the case study cards." |
| Thumbnail / Description | PlainText (single-line) | No | Yes | "Text featured underneath the image in the preview cards." |
| Article / Intro paragraph | PlainText (multi-line) | No | Yes | "Text underneath the heading before the main content." |
| Article / Main Content | RichText | No | Yes | "⚠️ Skip H1, instead start with a H2 heading" |
| Category | Reference | No | Yes | "Choose category tags to use to filter items." → refs `categories` collection |
| Featured? | Switch | No | Yes | "Toggling on will display said items in the Case Study header component." |
| Show First Stat | Switch | No | Yes | — |
| First Stat Number | PlainText (single-line) | No | Yes | — |
| First Stat Caption | PlainText (single-line) | No | Yes | — |
| Show Second Stat | Switch | No | Yes | — |
| Second Stat Number | PlainText (single-line) | No | Yes | — |
| Second Stat Caption | PlainText (single-line) | No | Yes | — |
| Show Third Stat | Switch | No | Yes | — |
| Third Stat Number | PlainText (single-line) | No | Yes | — |
| Third Stat Caption | PlainText (single-line) | No | Yes | — |
| Card Position | PlainText (single-line) | No | Yes | "⚠️ enter \"top\" or \"bottom\" to move the position of the stats on the page" |
| Name | PlainText | Yes | Yes | Max 256 chars |
| Slug | PlainText | Yes | Yes | Must be alphanumerical, no spaces or special characters. Max 256 chars |

---

## 5. ↳ Case Study Categories
- **Slug:** `categories`

| Field | Type | Required | Editable | Help Text / Validations |
|---|---|---|---|---|
| Name | PlainText | Yes | Yes | "Tag labels to be assigned to case study pages." Max 256 chars |
| Slug | PlainText | Yes | Yes | Must be alphanumerical, no spaces or special characters. Max 256 chars |

---

## 6. ↳ Pages
- **Slug:** `pages`

| Field | Type | Required | Editable | Help Text / Validations |
|---|---|---|---|---|
| Name | PlainText | Yes | Yes | Max 256 chars |
| Slug | PlainText | Yes | Yes | Must be alphanumerical, no spaces or special characters. Max 256 chars |

---

## 7. FAQs
- **Slug:** `faq`

| Field | Type | Required | Editable | Help Text / Validations |
|---|---|---|---|---|
| Answer | RichText | No | Yes | "Add answer here as long text content; revealed when the accordion drawer opens." |
| Order of Appearance | Number (decimal) | No | Yes | "Optional index to rank items by prioorty". No negatives, 1 decimal precision |
| Appears on page | MultiReference | No | Yes | "Optional filter to select entries to appear on specific pages; select from drop-down." → refs `pages` collection |
| Name | PlainText | Yes | Yes | Max 256 chars |
| Slug | PlainText | Yes | Yes | Must be alphanumerical, no spaces or special characters. Max 256 chars |

---

## 8. Features
- **Slug:** `features`

| Field | Type | Required | Editable | Help Text / Validations |
|---|---|---|---|---|
| Icon | Image | No | Yes | "Upload iconography as image here." |
| Paragraph | PlainText (multi-line) | No | Yes | "Long text paragraph appearing underneath the card heading." |
| Order of Appearance | Number (decimal) | No | Yes | "Optional index to rank items by priority." No negatives, 1 decimal precision |
| Category | Reference | No | Yes | "Choose relevant feature category from the drop-down list." → refs `feature-categories` collection |
| Related product | Reference | No | Yes | "Choose relevant product template to feature from the drop-down list." → refs `products` collection |
| Name | PlainText | Yes | Yes | Max 256 chars |
| Slug | PlainText | Yes | Yes | Must be alphanumerical, no spaces or special characters. Max 256 chars |

---

## 9. ↳ Feature Categories
- **Slug:** `feature-categories`

| Field | Type | Required | Editable | Help Text / Validations |
|---|---|---|---|---|
| Order of appearance | Number (decimal) | No | Yes | "Optional index to rank tabs by priority." No negatives, 1 decimal precision |
| Name | PlainText | Yes | Yes | "Add feature category to group by tabs in the product templates (where needed)." Max 256 chars |
| Slug | PlainText | Yes | Yes | Must be alphanumerical, no spaces or special characters. Max 256 chars |

---

## 10. Teams
- **Slug:** `team`

| Field | Type | Required | Editable | Help Text / Validations |
|---|---|---|---|---|
| Role | PlainText (single-line) | No | Yes | "Add their role here." |
| Bio | PlainText (multi-line) | No | Yes | "Long text field for adding a team member's biography synopsis." |
| Image | Image | Yes | Yes | "Add headshot image here" |
| Order of appearance | Number (decimal) | No | Yes | "Optional index to rank items by priority." No negatives, 1 decimal precision |
| Name | PlainText | Yes | Yes | Max 256 chars |
| Slug | PlainText | Yes | Yes | Must be alphanumerical, no spaces or special characters. Max 256 chars |

---

## 11. Footer Links
- **Slug:** `footer-links`

| Field | Type | Required | Editable | Help Text / Validations |
|---|---|---|---|---|
| Link | Link | No | Yes | "Add link address here" |
| Index | Number (integer) | No | Yes | "Optional index to rank items by priority." No negatives |
| Label | PlainText | Yes | Yes | "Label for the link as it appears in the footer." Max 256 chars |
| Slug | PlainText | Yes | Yes | Must be alphanumerical, no spaces or special characters. Max 256 chars |

---
