'use strict';

/**
 * @type {import("cspell").CSpellUserSettings}
 */
const config = {
  language: "en,en-GB",
  useGitignore: true,
  import: [
    "@androlax2/cspell-laravel/cspell-ext.json"
  ],
  dictionaries: [
    "software-term-suggestions",
    "softwareTerms",
    "software-tools",
    "networking-terms",
    "web-services",
    "computing-acronyms",
    "coding-compound-terms",
    "php",
    "docker",
    "npm"
  ],
  words: [
    "autolabeler",
    "prereleased"
  ],
  flagWords: [],
  ignoreWords: [
    "androlax",
    "Théo",
    "Benoit",
    "stefanzweifel",
    "shivammathur",
    "pipefail",
    "secretroot",
    "mysqladmin",
    "ARRAYACCESS"
  ],
  ignorePaths: [
    "tests"
  ]
};

module.exports = config;
