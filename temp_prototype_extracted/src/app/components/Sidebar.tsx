import React, { useState } from 'react';
import { Link, useNavigate, useLocation } from 'react-router';
import {
  LayoutDashboard,
  Calendar,
  ClipboardList,
  MapPin,
  MessageSquare,
  User,
  LogOut,
  Menu,
  X,
  Users,
  CheckSquare,
  Settings,
  BarChart3,
  Wrench,
  Sun,
  Moon,
} from 'lucide-react';
import { useTheme } from '../contexts/ThemeContext';

interface SidebarProps {
  role: 'customer' | 'staff' | 'mechanic' | 'admin';
}

export function Sidebar({ role }: SidebarProps) {
  const [isOpen, setIsOpen] = useState(false);
  const navigate = useNavigate();
  const location = useLocation();
  const { theme, toggleTheme } = useTheme();

  const menuItems = {
    customer: [
      { icon: LayoutDashboard, label: 'Dashboard', path: '/customer' },
      { icon: Calendar, label: 'Book Service', path: '/customer/book-service' },
      { icon: ClipboardList, label: 'My Bookings', path: '/customer/bookings' },
      { icon: MapPin, label: 'Track Service', path: '/customer/track' },
      { icon: MessageSquare, label: 'Support Tickets', path: '/customer/support' },
      { icon: User, label: 'Profile', path: '/customer/profile' },
    ],
    mechanic: [
      { icon: LayoutDashboard, label: 'Dashboard', path: '/mechanic' },
      { icon: Wrench, label: 'Assigned Jobs', path: '/mechanic/jobs' },
      { icon: ClipboardList, label: 'Service Notes', path: '/mechanic/notes' },
    ],
    staff: [
      { icon: LayoutDashboard, label: 'Dashboard', path: '/staff' },
      { icon: ClipboardList, label: 'Booking Queue', path: '/staff/booking-queue' },
      { icon: MessageSquare, label: 'Customer Assistance', path: '/staff/assistance' },
    ],
    admin: [
      { icon: LayoutDashboard, label: 'Dashboard', path: '/admin' },
      { icon: Users, label: 'User Management', path: '/admin/users' },
      { icon: CheckSquare, label: 'Booking Approval', path: '/admin/approvals' },
      { icon: Settings, label: 'Service Management', path: '/admin/services' },
      { icon: BarChart3, label: 'Reports', path: '/admin/reports' },
    ],
  };

  const items = menuItems[role];

  const handleLogout = () => {
    navigate('/login');
  };

  return (
    <>
      {/* Mobile Menu Button */}
      <button
        className="lg:hidden fixed top-4 left-4 z-50 p-3 rounded-xl bg-white dark:glass-card text-gray-900 dark:text-white shadow-lg border border-gray-300 dark:border-white/10"
        onClick={() => setIsOpen(!isOpen)}
      >
        {isOpen ? <X size={24} /> : <Menu size={24} />}
      </button>

      {/* Overlay */}
      {isOpen && (
        <div
          className="lg:hidden fixed inset-0 bg-black bg-opacity-70 z-30 backdrop-blur-sm"
          onClick={() => setIsOpen(false)}
        />
      )}

      {/* Sidebar */}
      <div
        className={`fixed top-0 left-0 h-full w-64 bg-white dark:glass-card border-r border-gray-300 dark:border-white/10 text-gray-900 dark:text-white z-40 transform transition-transform duration-300 ease-in-out shadow-2xl ${
          isOpen ? 'translate-x-0' : '-translate-x-full'
        } lg:translate-x-0`}
      >
        <div className="p-6 h-full flex flex-col">
          {/* Logo */}
          <div className="mb-8">
            <h1 className="text-2xl font-bold text-gray-900 dark:text-white tracking-wider">
              AUTO<span className="text-[#E63946]">PROJECT</span>+
            </h1>
            <p className="text-xs text-gray-600 dark:text-[#B8B8B8] mt-1 uppercase tracking-wider">
              {role} Portal
            </p>
          </div>

          {/* Navigation */}
          <nav className="space-y-1 flex-1">
            {items.map((item) => {
              const isActive = location.pathname === item.path;
              return (
                <Link
                  key={item.path}
                  to={item.path}
                  className={`flex items-center gap-4 px-4 py-3.5 rounded-xl transition-all duration-300 group ${
                    isActive
                      ? 'bg-[#E63946] text-white shadow-lg shadow-[#E63946]/20'
                      : 'text-gray-600 dark:text-[#B8B8B8] hover:bg-gray-100 dark:hover:bg-white/5 hover:text-gray-900 dark:hover:text-white'
                  }`}
                  onClick={() => setIsOpen(false)}
                >
                  <item.icon
                    size={20}
                    className={`${
                      isActive ? '' : 'group-hover:scale-110 transition-transform'
                    }`}
                  />
                  <span className="font-medium">{item.label}</span>
                </Link>
              );
            })}
          </nav>

          {/* Theme Toggle */}
          <button
            onClick={toggleTheme}
            className="flex items-center gap-4 px-4 py-3.5 rounded-xl transition-all duration-300 w-full text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-500/10 hover:text-gray-900 dark:hover:text-gray-300 border border-gray-300 dark:border-gray-500/20 hover:border-gray-400 dark:hover:border-gray-500/40 mb-2"
          >
            {theme === 'dark' ? <Sun size={20} /> : <Moon size={20} />}
            <span className="font-medium">{theme === 'dark' ? 'Light Mode' : 'Dark Mode'}</span>
          </button>

          {/* Logout Button */}
          <button
            onClick={handleLogout}
            className="flex items-center gap-4 px-4 py-3.5 rounded-xl transition-all duration-300 w-full text-red-500 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-500/10 hover:text-red-600 dark:hover:text-red-300 border border-red-300 dark:border-red-500/20 hover:border-red-400 dark:hover:border-red-500/40"
          >
            <LogOut size={20} />
            <span className="font-medium">Logout</span>
          </button>
        </div>
      </div>
    </>
  );
}