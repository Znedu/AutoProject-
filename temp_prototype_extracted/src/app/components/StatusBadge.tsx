import React from 'react';

interface StatusBadgeProps {
  status:
    | 'pending'
    | 'approved'
    | 'rejected'
    | 'waiting-payment'
    | 'confirmed'
    | 'in-progress'
    | 'completed'
    | 'open'
    | 'resolved';
  children: React.ReactNode;
}

export function StatusBadge({ status, children }: StatusBadgeProps) {
  const styles = {
    pending: 'bg-yellow-500/10 text-yellow-500 border-yellow-500/20',
    approved: 'bg-green-500/10 text-green-500 border-green-500/20',
    rejected: 'bg-red-500/10 text-red-500 border-red-500/20',
    'waiting-payment': 'bg-orange-500/10 text-orange-500 border-orange-500/20',
    confirmed: 'bg-blue-500/10 text-blue-500 border-blue-500/20',
    'in-progress': 'bg-purple-500/10 text-purple-500 border-purple-500/20',
    completed: 'bg-green-500/10 text-green-500 border-green-500/20',
    open: 'bg-blue-500/10 text-blue-500 border-blue-500/20',
    resolved: 'bg-green-500/10 text-green-500 border-green-500/20',
  };

  return (
    <span
      className={`inline-flex items-center px-4 py-1.5 rounded-full text-sm font-semibold border ${styles[status]}`}
    >
      {children}
    </span>
  );
}
