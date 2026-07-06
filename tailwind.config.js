import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
    ],

    theme: {
        extend: {
            fontFamily: {
                sans: ['Outfit', ...defaultTheme.fontFamily.sans],
            },
            colors: {
                picotee: '#2E2787',
                primary: {
                    50: '#eff6ff', 100: '#dbeafe', 500: '#3b82f6', 600: '#2563eb', 700: '#1d4ed8'
                },
                secondary: {
                    500: '#10b981', 600: '#059669',
                },
                doctor: { 500: '#0ea5e9', 600: '#0284c7' },
                pharmacy: { 500: '#f59e0b', 600: '#d97706' },
                reception: { 500: '#ec4899', 600: '#db2777' },
                lab: { 500: '#8b5cf6', 600: '#7c3aed' },
                nurse: { 500: '#10b981', 600: '#059669' },
                admin: { 500: '#6366f1', 600: '#4f46e5' },
                'tech-blue': { 50: '#f0f9ff', 100: '#e0f2fe', 500: '#0ea5e9', 600: '#0284c7' },
                md: {
                    green: '#4caf50', teal: '#009688', cyan: '#00bcd4', blue: '#2196f3',
                    navy: '#3c4858', indigo: '#3f51b5', purple: '#9c27b0', red: '#f44336',
                    rose: '#e91e63', yellow: '#ffeb3b', orange: '#ff9800', black: '#212529',
                    gray: '#6c757d', 'light-gray': '#d2d2d2', white: '#fff',
                },
                'purple-dark': {
                    50: '#F4F2FA', 100: '#EAE7F5', 200: '#D0C9E8', 300: '#B6B0D9', 400: '#9D97C5',
                    500: '#837BC0', 600: '#6861A2', 700: '#4E4685', 800: '#342E67', 900: '#1A174A', 950: '#0E0C2B',
                },
                navy: {
                    50: '#e0e3f1', 100: '#c2c7e3', 200: '#a3adce', 300: '#8592b9', 400: '#6676a4',
                    500: '#485b8f', 600: '#2a407a', 700: '#1a326d', 800: '#12245c', 900: '#0a1c2f', 950: '#07104a',
                },
                jade: {
                    50: '#BDFFDF', 100: '#6CFFC2', 200: '#0DE8A4', 300: '#0ACC90', 400: '#07B17C',
                    500: '#059669', 600: '#037753', 700: '#02593D', 800: '#013D29', 900: '#002316', 950: '#00150B'
                },
                maroon: {
                    50: '#FEF1F1', 100: '#FCDEDE', 200: '#FAC1C1', 300: '#F89D9D', 400: '#F77B7B',
                    500: '#F64A4A', 600: '#E22525', 700: '#B91C1C', 800: '#811010', 900: '#490505', 950: '#330303'
                },
                'violet-x': {
                    50: '#F2EFFE', 100: '#E5DFFD', 200: '#CBBEFB', 300: '#B39DFA', 400: '#9D7BF8',
                    500: '#8856F5', 600: '#7422F1', 700: '#5817BA', 800: '#3D0D85', 900: '#240554', 950: '#17023B'
                },
            },
            boxShadow: {
                'soft': '0 4px 20px -2px rgba(0, 0, 0, 0.05)',
                'glow': '0 0 15px rgba(59, 130, 246, 0.2)',
            },
            animation: {
                'fade-in': 'fadeIn 0.5s ease-out',
            },
            keyframes: {
                fadeIn: {
                    '0%': { opacity: '0', transform: 'translateY(10px)' },
                    '100%': { opacity: '1', transform: 'translateY(0)' },
                }
            }
        },
    },

    plugins: [forms],
};
