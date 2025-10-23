# CBTA 71 Frontend

## Overview
This project is a frontend application for managing concepts related to educational fees and donations at CBTA 71. It allows users to view, add, edit, and filter concepts.

## Project Structure
```
cbta71-frontend
├── src
│   ├── pages
│   │   ├── concepts.astro         # Displays a list of concepts with filtering options.
│   │   ├── new.astro              # Form for adding or editing concepts.
│   │   └── concepts
│   │       └── edit
│   │           └── [id].astro      # Edits a specific concept based on its ID.
│   ├── layouts
│   │   └── Layout.astro            # Defines the layout structure for the pages.
│   ├── components
│   │   ├── ConceptCard.astro       # Component for displaying individual concept cards.
│   │   └── ConceptForm.astro       # Form component for adding or editing concepts.
│   ├── lib
│   │   └── concepts.ts             # Utility functions for managing concepts.
│   └── types
│       └── index.ts                # TypeScript types and interfaces related to concepts.
├── package.json                     # Configuration file for npm.
├── astro.config.mjs                # Configuration settings for the Astro framework.
├── tsconfig.json                   # Configuration file for TypeScript.
├── tailwind.config.cjs             # Configuration settings for Tailwind CSS.
└── README.md                       # Documentation for the project.
```

## Setup Instructions
1. Clone the repository:
   ```
   git clone <repository-url>
   cd cbta71-frontend
   ```

2. Install dependencies:
   ```
   npm install
   ```

3. Start the development server:
   ```
   npm run dev
   ```

4. Open your browser and navigate to `http://localhost:3000` to view the application.

## Usage
- Navigate to the **Concepts** page to view all concepts.
- Use the **Add** button to create a new concept.
- Click on **Edit** to modify an existing concept.
- Filter concepts by their status (Active or Finished).

## Contributing
Contributions are welcome! Please open an issue or submit a pull request for any enhancements or bug fixes.

## License
This project is licensed under the MIT License. See the LICENSE file for more details.