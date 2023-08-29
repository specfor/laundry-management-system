/** @type {import('tailwindcss').Config} */
export default {
    content: [
        "./index.html",
        "./src/**/*.{vue,js,ts,jsx,tsx}",
    ],
    theme: {
        extend: {
            container: {
                center: true,
                padding: '2rem'
            },
            extend: {
                screens: {
                    'xs': '475px'
                }
            },
        },
    },
    plugins: [require("daisyui")],
    daisyui: {
        themes: ["corporate"],
    },
}

