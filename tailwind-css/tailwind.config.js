/** @type {import('tailwindcss').Config} */
module.exports = {
    content: {
        relative: true,
        files: [
            './../website/assets/js/*.js',
            './../website/views/*.php',
            './../website/views/**/*.php'
        ]
    },
    theme: {
        extend: {},
    },
    plugins: [],
}

