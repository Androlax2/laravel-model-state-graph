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
    "pipefail",
    "prereleased",
    "stefanzweifel",
    "shivammathur"
  ],
  flagWords: [],
  ignoreWords: [
    "androlax",
    "Théo",
    "Benoit"
  ],
  ignorePaths: [
    "tests"
  ]
};

module.exports = config;
