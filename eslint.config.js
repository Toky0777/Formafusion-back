import pluginJs from "@eslint/js";

export default [
  pluginJs.configs.recommended,

  {
    rules: {
      "no-unused-vars": "warn",
      "no-undef": "warn",
      "no-console": ["warn", { "allow": ["warn", "error"] }],
      "prefer-const": ["warn"],
      "no-duplicate-imports": "warn",
      "no-redeclare": "warn",
      "eqeqeq": ["error", "always"],
      "complexity": ["warn", { "max": 10 }],
      "max-lines": ["warn", { "max": 100 }],
      "no-throw-literal": "error",
      "no-inner-declarations": ["error", "functions"],
      "no-magic-numbers": "warn",
    }
  }
];
