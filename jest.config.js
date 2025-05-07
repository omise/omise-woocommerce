const defaultConfig = require('@wordpress/scripts/config/jest-unit.config');

const config = {
  ...defaultConfig,
  setupFilesAfterEnv: [
    '<rootDir>/tests/js/setup.js',
  ],
  transform: {
    '^.+\\.(js|jsx|ts|tsx)$': ['babel-jest', { configFile: './tests/js/babel.config.js' }],
  },
  testPathIgnorePatterns: [
    '<rootDir>/node_modules/',
    '<rootDir>/wp-content/',
  ],
};

module.exports = config;
