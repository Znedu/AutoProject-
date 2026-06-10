import React from 'react';

interface ButtonProps extends React.ButtonHTMLAttributes<HTMLButtonElement> {
  variant?: 'primary' | 'secondary' | 'accent' | 'outline' | 'ghost';
  size?: 'sm' | 'md' | 'lg';
  children: React.ReactNode;
}

export function Button({
  variant = 'primary',
  size = 'md',
  className = '',
  children,
  ...props
}: ButtonProps) {
  const baseStyles =
    'inline-flex items-center justify-center rounded-xl font-semibold transition-all duration-300 disabled:opacity-50 disabled:cursor-not-allowed';

  const variants = {
    primary:
      'bg-gray-200 dark:bg-[#151515] text-gray-900 dark:text-white border border-gray-300 dark:border-white/10 hover:border-[#E63946] hover:shadow-lg hover:shadow-[#E63946]/20',
    secondary:
      'bg-gray-300 dark:bg-[#2F2F2F] text-gray-900 dark:text-white border border-gray-400 dark:border-white/10 hover:border-gray-500 dark:hover:border-white/30 hover:shadow-lg',
    accent:
      'bg-gradient-red text-white hover:shadow-xl hover:shadow-[#E63946]/50 glow-red-hover',
    outline:
      'border-2 border-gray-400 dark:border-white/20 text-gray-900 dark:text-white hover:border-[#E63946] hover:text-[#E63946] hover:shadow-lg hover:shadow-[#E63946]/20',
    ghost: 'text-gray-600 dark:text-[#B8B8B8] hover:text-gray-900 dark:hover:text-white hover:bg-gray-100 dark:hover:bg-white/5',
  };

  const sizes = {
    sm: 'px-4 py-2 text-sm',
    md: 'px-6 py-3 text-base',
    lg: 'px-8 py-4 text-lg',
  };

  return (
    <button className={`${baseStyles} ${variants[variant]} ${sizes[size]} ${className}`} {...props}>
      {children}
    </button>
  );
}