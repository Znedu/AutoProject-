import React from 'react';

interface InputProps extends React.InputHTMLAttributes<HTMLInputElement> {
  label?: string;
  error?: string;
  helperText?: string;
}

export function Input({ label, error, helperText, className = '', ...props }: InputProps) {
  return (
    <div className="w-full">
      {label && (
        <label className="block mb-2 text-gray-700 dark:text-[#B8B8B8] font-medium">
          {label}
          {props.required && <span className="text-[#E63946] ml-1">*</span>}
        </label>
      )}
      <input
        className={`w-full px-4 py-3 rounded-xl border border-gray-300 dark:border-white/10 bg-white dark:bg-[#1F1F1F] text-gray-900 dark:text-white placeholder:text-gray-400 dark:placeholder:text-[#666666] focus:outline-none focus:ring-2 focus:ring-[#E63946] focus:border-transparent transition-all ${
          error ? 'border-red-500 ring-2 ring-red-500/20' : ''
        } ${className}`}
        {...props}
      />
      {error && <p className="mt-2 text-sm text-red-500">{error}</p>}
      {!error && helperText && <p className="mt-2 text-sm text-gray-600 dark:text-gray-400">{helperText}</p>}
    </div>
  );
}

interface TextAreaProps extends React.TextareaHTMLAttributes<HTMLTextAreaElement> {
  label?: string;
  error?: string;
}

export function TextArea({ label, error, className = '', ...props }: TextAreaProps) {
  return (
    <div className="w-full">
      {label && (
        <label className="block mb-2 text-gray-700 dark:text-[#B8B8B8] font-medium">
          {label}
          {props.required && <span className="text-[#E63946] ml-1">*</span>}
        </label>
      )}
      <textarea
        className={`w-full px-4 py-3 rounded-xl border border-gray-300 dark:border-white/10 bg-white dark:bg-[#1F1F1F] text-gray-900 dark:text-white placeholder:text-gray-400 dark:placeholder:text-[#666666] focus:outline-none focus:ring-2 focus:ring-[#E63946] focus:border-transparent transition-all resize-none ${
          error ? 'border-red-500 ring-2 ring-red-500/20' : ''
        } ${className}`}
        {...props}
      />
      {error && <p className="mt-2 text-sm text-red-500">{error}</p>}
    </div>
  );
}

interface SelectProps extends React.SelectHTMLAttributes<HTMLSelectElement> {
  label?: string;
  error?: string;
  options: { value: string; label: string }[];
}

export function Select({ label, error, options, className = '', ...props }: SelectProps) {
  return (
    <div className="w-full">
      {label && (
        <label className="block mb-2 text-gray-700 dark:text-[#B8B8B8] font-medium">
          {label}
          {props.required && <span className="text-[#E63946] ml-1">*</span>}
        </label>
      )}
      <select
        className={`w-full px-4 py-3 rounded-xl border border-gray-300 dark:border-white/10 bg-white dark:bg-[#1F1F1F] text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-[#E63946] focus:border-transparent transition-all ${
          error ? 'border-red-500 ring-2 ring-red-500/20' : ''
        } ${className}`}
        {...props}
      >
        {options.map((option) => (
          <option key={option.value} value={option.value} className="bg-white dark:bg-[#1F1F1F] text-gray-900 dark:text-white">
            {option.label}
          </option>
        ))}
      </select>
      {error && <p className="mt-2 text-sm text-red-500">{error}</p>}
    </div>
  );
}