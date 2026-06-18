/** @type {import('tailwindcss').Config} */
module.exports = {
  content: [
    './views/**/*.php',
    './public/assets/js/**/*.js',
    './user-guide.html',
  ],
  darkMode: 'class',
  theme: {
    extend: {
      colors: {
        primary: {
          50: '#F0F0FF',
          100: '#E1E1FF',
          200: '#C2C3FF',
          300: '#A4A5FF',
          400: '#8586FF',
          500: '#6367FF',
          600: '#4D51E6',
          700: '#363AC0',
          800: '#1F2299',
          900: '#080B73',
        },
      },
    },
  },
  plugins: [],
};
