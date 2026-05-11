/** @type {import('tailwindcss').Config} */
module.exports = {
    content: [
        './resources/**/*.blade.php',
        './resources/**/*.js',
    ],
    theme: {
        extend: {
            colors: {
                ivory: {
                    DEFAULT: '#F5F5F7',
                    dark: '#EBEBEE',
                },
                'misty-blue': {
                    DEFAULT: '#2F5061',
                    light: '#3D6478',
                    dark: '#1E3340',
                },
                'teal-green': {
                    DEFAULT: '#4297A0',
                    light: '#5BB0BA',
                    dark: '#317880',
                },
                coral: {
                    DEFAULT: '#E57F84',
                    light: '#EDA0A4',
                    dark: '#D45E64',
                },
            },
            fontFamily: {
                heading: ['Barlow', 'sans-serif'],
                body: ['"IBM Plex Sans"', 'sans-serif'],
            },
            borderRadius: {
                btn: '8px',
                card: '12px',
                media: '16px',
            },
            maxWidth: {
                content: '1200px',
            },
            spacing: {
                // 8pt rhythm aliases
                '18': '4.5rem',
                '22': '5.5rem',
            },
            transitionDuration: {
                '250': '250ms',
                '350': '350ms',
            },
        },
    },
    plugins: [],
};