import React from 'react';

interface CardProps {
  children: React.ReactNode;
  className?: string;
  hover?: boolean;
  variant?: 'default' | 'glass';
}

export function Card({ children, className = '', hover = false, variant = 'glass' }: CardProps) {
  const variantClasses = {
    default: 'bg-white dark:bg-[#151515] border border-gray-300 dark:border-white/10',
    glass: 'bg-white/80 dark:bg-[#151515]/70 backdrop-blur-md border border-gray-200 dark:border-white/10 shadow-sm dark:shadow-[0_8px_32px_0_rgba(0,0,0,0.37)]',
  };

  const hoverClasses = hover 
    ? 'hover:shadow-xl dark:hover:shadow-[0_8px_32px_0_rgba(230,57,70,0.2)] hover:border-[#E63946]/30 dark:hover:border-[#E63946]/50 hover:-translate-y-0.5' 
    : '';

  return (
    <div
      className={`rounded-xl p-6 transition-all duration-300 ${variantClasses[variant]} ${hoverClasses} ${className} bg-[#ffffffcc]`}
    >
      {children}
    </div>
  );
}

interface StatCardProps {
  title: string;
  value: string | number;
  icon: React.ReactNode;
  trend?: {
    value: string;
    isPositive: boolean;
  };
  color?: 'red' | 'blue' | 'charcoal' | 'green';
}

export function StatCard({ title, value, icon, trend, color = 'red' }: StatCardProps) {
  const colors = {
    red: 'bg-[#E63946]/10 text-[#E63946] border border-[#E63946]/20',
    blue: 'bg-[#457B9D]/10 text-[#457B9D] border border-[#457B9D]/20',
    charcoal: 'bg-gray-200 dark:bg-white/5 text-gray-900 dark:text-white border border-gray-300 dark:border-white/10',
    green: 'bg-green-500/10 text-green-500 border border-green-500/20',
  };

  return (
    <Card className="flex items-start gap-4 hover:scale-105 cursor-pointer" hover>
      <div className={`p-4 rounded-xl ${colors[color]}`}>{icon}</div>
      <div className="flex-1">
        <p className="text-sm text-gray-600 dark:text-[#B8B8B8] mb-1">{title}</p>
        <p className="text-3xl font-bold text-gray-900 dark:text-white">{value}</p>
        {trend && (
          <p
            className={`text-sm mt-2 font-medium ${
              trend.isPositive ? 'text-green-500' : 'text-red-500'
            }`}
          >
            {trend.isPositive ? '↑' : '↓'} {trend.value}
          </p>
        )}
      </div>
    </Card>
  );
}