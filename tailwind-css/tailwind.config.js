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
        container: {
            center: true,
            padding: '2rem'
        },
        extend: {
            screens:{
                'xs': '475px'
            }
        },
    },
    plugins: [],
}

