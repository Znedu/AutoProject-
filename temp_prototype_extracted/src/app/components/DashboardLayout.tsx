import React from 'react';
import { Sidebar } from './Sidebar';

interface DashboardLayoutProps {
  role: 'customer' | 'staff' | 'mechanic' | 'admin';
  children: React.ReactNode;
}

export function DashboardLayout({ role, children }: DashboardLayoutProps) {
  return (
    <div className="min-h-screen bg-white dark:bg-[#0B0B0B] transition-colors duration-200">
      <Sidebar role={role} />
      <div className="lg:ml-64">
        <main className="p-6 lg:p-8">{children}</main>
      </div>
    </div>
  );
}