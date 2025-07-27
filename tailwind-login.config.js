/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './resources/views/filament/auth/**/*.blade.php',
        './app/Providers/Filament/AdminPanelProvider.php',
    ],
    theme: {
        extend: {
            fontFamily: {
                sans: ['Inter', 'ui-sans-serif', 'system-ui', '-apple-system', 'BlinkMacSystemFont', 'Segoe UI', 'Roboto', 'Helvetica Neue', 'Arial', 'Noto Sans', 'sans-serif'],
            },
        },
    },
    plugins: [
        // Simulamos DaisyUI con componentes básicos
        function({ addComponents, theme }) {
            addComponents({
                // Card components
                '.card': {
                    'border-radius': theme('borderRadius.2xl'),
                    'background-color': theme('colors.white'),
                    'box-shadow': theme('boxShadow.xl'),
                    'border': `1px solid ${theme('colors.gray.200')}`,
                },
                '.card-body': {
                    'padding': theme('spacing.8'),
                },
                
                // Button components
                '.btn': {
                    'display': 'inline-flex',
                    'align-items': 'center',
                    'justify-content': 'center',
                    'border-radius': theme('borderRadius.lg'),
                    'font-weight': theme('fontWeight.medium'),
                    'transition': 'all 0.2s cubic-bezier(0.4, 0, 0.2, 1)',
                    'cursor': 'pointer',
                    'border': 'none',
                    'text-decoration': 'none',
                    'gap': theme('spacing.2'),
                    'padding': `${theme('spacing.3')} ${theme('spacing.6')}`,
                    'font-size': theme('fontSize.base'),
                    'line-height': theme('lineHeight.6'),
                },
                '.btn-primary': {
                    'background-color': theme('colors.blue.600'),
                    'color': theme('colors.white'),
                    'box-shadow': theme('boxShadow.sm'),
                    '&:hover': {
                        'background-color': theme('colors.blue.700'),
                        'transform': 'translateY(-1px)',
                        'box-shadow': theme('boxShadow.md'),
                    },
                    '&:active': {
                        'background-color': theme('colors.blue.800'),
                        'transform': 'translateY(0)',
                        'box-shadow': theme('boxShadow.sm'),
                    },
                },
                '.btn-block': {
                    'width': '100%',
                },
                '.btn-disabled': {
                    'opacity': '0.6',
                    'cursor': 'not-allowed',
                    'transform': 'none !important',
                },
                
                // Input components
                '.input': {
                    'width': '100%',
                    'padding': `${theme('spacing.3')} ${theme('spacing.4')}`,
                    'border-radius': theme('borderRadius.lg'),
                    'border': `1px solid ${theme('colors.gray.300')}`,
                    'background-color': theme('colors.white'),
                    'font-size': theme('fontSize.base'),
                    'line-height': theme('lineHeight.6'),
                    'transition': 'all 0.2s cubic-bezier(0.4, 0, 0.2, 1)',
                    'box-shadow': theme('boxShadow.xs'),
                    '&:focus': {
                        'outline': 'none',
                        'border-color': theme('colors.blue.500'),
                        'box-shadow': `0 0 0 3px ${theme('colors.blue.100')}, ${theme('boxShadow.sm')}`,
                    },
                    '&:hover:not(:focus)': {
                        'border-color': theme('colors.gray.400'),
                    },
                },
                '.input-bordered': {
                    'border': `1px solid ${theme('colors.gray.300')}`,
                },
                '.input-primary': {
                    'border-color': theme('colors.blue.500'),
                    'box-shadow': `0 0 0 3px ${theme('colors.blue.100')}`,
                },
                '.input-error': {
                    'border-color': theme('colors.red.500'),
                    'box-shadow': `0 0 0 3px ${theme('colors.red.100')}`,
                },
                '.input-disabled': {
                    'background-color': theme('colors.gray.100'),
                    'color': theme('colors.gray.400'),
                    'cursor': 'not-allowed',
                },
                
                // Badge components
                '.badge': {
                    'display': 'inline-flex',
                    'align-items': 'center',
                    'gap': theme('spacing.2'),
                    'padding': `${theme('spacing.2')} ${theme('spacing.3')}`,
                    'border-radius': theme('borderRadius.md'),
                    'font-size': theme('fontSize.xs'),
                    'font-weight': theme('fontWeight.medium'),
                    'line-height': theme('lineHeight.4'),
                },
                '.badge-primary': {
                    'background-color': theme('colors.blue.600'),
                    'color': theme('colors.white'),
                },
                '.badge-outline': {
                    'background-color': theme('colors.blue.50'),
                    'color': theme('colors.blue.700'),
                    'border': `1px solid ${theme('colors.blue.200')}`,
                },
                
                // Form components
                '.form-control': {
                    'display': 'flex',
                    'flex-direction': 'column',
                    'gap': theme('spacing.2'),
                },
                '.label': {
                    'display': 'flex',
                    'align-items': 'center',
                    'gap': theme('spacing.2'),
                    'cursor': 'pointer',
                },
                '.label-text': {
                    'font-size': theme('fontSize.sm'),
                    'font-weight': theme('fontWeight.medium'),
                    'color': theme('colors.gray.700'),
                    'letter-spacing': '0.025em',
                },
                
                // Checkbox components
                '.checkbox': {
                    'width': '18px',
                    'height': '18px',
                    'border': `1px solid ${theme('colors.gray.300')}`,
                    'border-radius': theme('borderRadius.sm'),
                    'background-color': theme('colors.white'),
                    'cursor': 'pointer',
                    'transition': 'all 0.2s cubic-bezier(0.4, 0, 0.2, 1)',
                    '&:checked': {
                        'background-color': theme('colors.blue.600'),
                        'border-color': theme('colors.blue.600'),
                    },
                },
                '.checkbox-primary': {
                    '&:checked': {
                        'background-color': theme('colors.blue.600'),
                        'border-color': theme('colors.blue.600'),
                    },
                },
                
                // Link components
                '.link': {
                    'text-decoration': 'none',
                    'transition': 'all 0.2s cubic-bezier(0.4, 0, 0.2, 1)',
                    '&:hover': {
                        'text-decoration': 'underline',
                    },
                },
                '.link-primary': {
                    'color': theme('colors.blue.600'),
                    '&:hover': {
                        'color': theme('colors.blue.700'),
                    },
                },
                
                // Base colors
                '.bg-base-100': {
                    'background-color': theme('colors.white'),
                },
                '.bg-base-200': {
                    'background-color': theme('colors.gray.50'),
                },
                '.bg-base-300': {
                    'background-color': theme('colors.gray.100'),
                },
                '.text-base-content': {
                    'color': theme('colors.gray.900'),
                },
                '.text-error': {
                    'color': theme('colors.red.600'),
                },
            });
        }
    ],
};
