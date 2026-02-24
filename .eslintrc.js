module.exports = {
  // Type de configuration
  env: {
    browser: true,
    node: true,
    es2021: true
  },
  extends: [
    'eslint:recommended', // Utilise les règles de base recommandées par ESLint
    'plugin:prettier/recommended' // Si tu utilises Prettier, tu peux l'ajouter
  ],
  parserOptions: {
    ecmaVersion: 12,
    sourceType: 'module'
  },
  plugins: [
    "optimize-regex",
    "performance"
  ],
  rules: {
    // Ici, tu peux ajouter ou modifier les règles spécifiques
    "no-console": ["warn", { "allow": ["warn", "error"] }],
    "prefer-const": ["warn"],
    "eqeqeq": ["error", "always"],
    "complexity": ["warn", { "max": 10 }],
    "max-lines": ["warn", { "max": 100 }],
    "no-duplicate-imports": "warn",
    "no-inner-declarations": ["error", "functions"],
    "optimize-regex/optimize-regex": "warn",

    "performance/no-large-loops": "warn",
    "performance/no-bad-iteration": "warn"
  }
};
