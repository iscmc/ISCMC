/** @type {import('tailwindcss').Config} */
module.exports = {
  // **IMPORTANTE**: Garanta que o caminho de conteúdo esteja correto!
  content: [
    "../**/*.{html,js,cshtml,php}", 
  ],
  theme: {
    extend: {
      colors: {
        primary: '#FA0F0F',
        'primary-dark': '#CC0C0C',
        secondary: '#6B7280',
        success: '#0d9488',
        danger: '#EF4444',
        warning: '#F59E0B',
        info: '#0EA5E9',
      },
    },
  },
  plugins: [],
}