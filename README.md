# Galant Warszawski - Static Website

This is a static version of the Galant Warszawski restaurant website, converted from PHP to HTML/CSS/JavaScript for hosting on GitHub Pages.

## Structure

```
├── index.html          # Polish version (default)
├── index-en.html       # English version
├── assets/
│   ├── styles.css          # Main styles
│   ├── styles-scrolly.css  # Scrollytelling effect styles
│   ├── styles-modal.css    # Menu modal styles
│   ├── scrollytelling.js   # Image scroll effect
│   ├── menu-modal.js       # Menu with categories and products
│   ├── main.js             # Language switching
│   ├── *.jpg               # Images
│   ├── logo.png
│   └── favicon.png
└── .gitignore
```

## Features

- **Scrollytelling Effect**: Images change as you scroll through sections
- **Menu Modal**: Interactive menu with 6 categories and 33 products
- **Bilingual Support**: Polish (index.html) and English (index-en.html)
- **Responsive Design**: Works on desktop and mobile
- **Google Maps Integration**: Embedded map showing restaurant location

## Deployment to GitHub Pages

1. Push all files to your GitHub repository
2. Go to repository Settings → Pages
3. Select source branch (usually `main` or `master`)
4. Your site will be available at: `https://<username>.github.io/<repository-name>/`

## Important Notes

- All PHP files and admin functionality have been removed
- User authentication and admin panel are not included (not needed for public website)
- Menu data is embedded directly in JavaScript (no external JSON fetching)
- Images are loaded from the assets/ directory

## Content Management

To update content in the future:
- Edit text directly in `index.html` (Polish) or `index-en.html` (English)
- Menu items are in `assets/menu-modal.js` (categories and products arrays)
- Images can be replaced in the `assets/` folder

## Browser Support

- Modern browsers with Intersection Observer support (Chrome, Firefox, Safari, Edge)
- Mobile responsive design