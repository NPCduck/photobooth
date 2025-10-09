import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
        './resources/js/**/*.vue',
    ],

    theme: {
        extend: {
            fontFamily: {
                sans: ['Figtree', ...defaultTheme.fontFamily.sans],
            },
            colors: {
                sidebarbg: {
                    DEFAULT: '#2f2f2f',
                    dark: '#232323',
                },
                overlaybg: {
                    DEFAULT: '#F0EDED',
                },
                highlight: {
                    DEFAULT: '#505050'
                },
                itembg: {
                    DEFAULT: 'rgba(0, 0, 0, .05)',
                },
                text: '#232323',
                
            }
        },
    },

    plugins: [forms],
};
