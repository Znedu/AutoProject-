import { useState } from 'react';
import { Link, useNavigate } from 'react-router';
import { Button } from '../components/Button';
import { Input } from '../components/FormInputs';
import { LogIn, User, Shield, Wrench, Users, Info, ArrowRight } from 'lucide-react';
import { showToast } from '../utils/toast';

export default function Login() {
  const [email, setEmail] = useState('');
  const [password, setPassword] = useState('');
  const navigate = useNavigate();

  // Demo accounts for each role
  const demoAccounts = {
    'customer@gmail.com': { role: 'customer', path: '/customer', name: 'Customer' },
    'staff@gmail.com': { role: 'staff', path: '/staff', name: 'Staff' },
    'mechanic@gmail.com': { role: 'mechanic', path: '/mechanic', name: 'Mechanic' },
    'admin@gmail.com': { role: 'admin', path: '/admin', name: 'Administrator' },
  };

  const handleLogin = (e: React.FormEvent) => {
    e.preventDefault();

    // Check if email matches a demo account
    const account = demoAccounts[email.toLowerCase() as keyof typeof demoAccounts];

    if (account) {
      // Validate password (demo: any password works for demo accounts)
      if (password) {
        showToast.success(`Welcome back, ${account.name}!`);
        navigate(account.path);
      } else {
        showToast.error('Please enter a password');
      }
    } else {
      showToast.error('Invalid email address. Please use a demo account.');
    }
  };

  const handleDemoLogin = (email: string) => {
    const account = demoAccounts[email as keyof typeof demoAccounts];
    if (account) {
      showToast.success(`Welcome back, ${account.name}!`);
      navigate(account.path);
    }
  };

  const demoButtons = [
    {
      email: 'customer@gmail.com',
      label: 'Customer',
      icon: User,
      color: 'from-blue-600 to-blue-700',
    },
    {
      email: 'staff@gmail.com',
      label: 'Staff',
      icon: Users,
      color: 'from-green-600 to-green-700',
    },
    {
      email: 'mechanic@gmail.com',
      label: 'Mechanic',
      icon: Wrench,
      color: 'from-purple-600 to-purple-700',
    },
    {
      email: 'admin@gmail.com',
      label: 'Admin',
      icon: Shield,
      color: 'from-[#E63946] to-red-700',
    },
  ];

  return (
    <div
      className="min-h-screen flex items-center justify-center p-4 relative overflow-hidden"
      style={{
        backgroundImage: `url('https://images.unsplash.com/photo-1768387666438-b3da75373846?crop=entropy&cs=tinysrgb&fit=max&fm=jpg&q=80&w=1920')`,
        backgroundSize: 'cover',
        backgroundPosition: 'center',
        backgroundAttachment: 'fixed',
      }}
    >
      {/* Dark Overlay */}
      <div className="absolute inset-0 bg-gradient-to-br from-black/90 via-black/85 to-black/90"></div>

      <div className="relative z-10 w-full max-w-md">
        {/* Logo */}
        <div className="text-center mb-8">
          <h1 className="text-5xl font-bold text-white mb-2 tracking-wider">
            AUTO<span className="text-[#E63946] text-glow">PROJECT</span>+
          </h1>
          <p className="text-[#B8B8B8] text-lg">Welcome back! Please login to continue.</p>
        </div>

        {/* Login Card */}
        <div className="glass-card p-8 rounded-2xl">
          <h2 className="text-2xl font-bold text-white mb-6">Login to Your Account</h2>

          {/* Login Form */}
          <form onSubmit={handleLogin} className="space-y-5">
            <Input
              label="Email Address"
              type="email"
              placeholder="Enter your email"
              value={email}
              onChange={(e) => setEmail(e.target.value)}
              required
            />

            <Input
              label="Password"
              type="password"
              placeholder="Enter your password"
              value={password}
              onChange={(e) => setPassword(e.target.value)}
              required
            />

            <Button type="submit" variant="accent" size="lg" className="w-full group">
              <LogIn size={20} className="mr-2" />
              Login to Dashboard
              <ArrowRight
                size={18}
                className="ml-2 group-hover:translate-x-1 transition-transform"
              />
            </Button>
          </form>

          {/* Divider */}
          <div className="relative my-6">
            <div className="absolute inset-0 flex items-center">
              <div className="w-full border-t border-white/10"></div>
            </div>
            <div className="relative flex justify-center text-sm">
              <span className="px-4 bg-[#151515] text-[#B8B8B8]">Or quick login as</span>
            </div>
          </div>

          {/* Quick Login Buttons */}
          <div className="grid grid-cols-2 gap-3">
            {demoButtons.map((demo) => (
              <button
                key={demo.email}
                onClick={() => handleDemoLogin(demo.email)}
                className={`group p-4 rounded-xl bg-gradient-to-br ${demo.color} text-white font-semibold hover:scale-105 transition-all duration-300 shadow-lg hover:shadow-xl`}
              >
                <demo.icon size={24} className="mx-auto mb-2" />
                <div className="text-sm">{demo.label}</div>
              </button>
            ))}
          </div>

          {/* Register Link */}
          <div className="mt-6 text-center">
            <p className="text-[#B8B8B8]">
              Don't have an account?{' '}
              <Link
                to="/register"
                className="text-[#E63946] font-semibold hover:underline transition-colors"
              >
                Create Account
              </Link>
            </p>
          </div>

          {/* Info Banner - Demo Account Access (moved to bottom) */}
          <div className="mt-6 bg-blue-500/10 border-l-4 border-blue-500 p-4 rounded-xl">
            <div className="flex gap-3">
              <Info className="text-blue-500 flex-shrink-0 mt-0.5" size={20} />
              <div className="text-sm text-[#B8B8B8]">
                <p className="font-medium text-white mb-2">Demo Account Access</p>
                <p className="text-xs mb-3">
                  Use the quick login buttons above or enter credentials manually.
                </p>
                <div className="bg-black/30 rounded-lg p-3 border border-white/10">
                  <p className="text-xs font-medium text-white mb-2">Available Demo Accounts:</p>
                  <div className="space-y-1.5 font-mono text-xs">
                    <div className="flex justify-between items-center">
                      <span className="text-[#B8B8B8]">customer@gmail.com</span>
                      <span className="text-[#666666]">→ Customer</span>
                    </div>
                    <div className="flex justify-between items-center">
                      <span className="text-[#B8B8B8]">staff@gmail.com</span>
                      <span className="text-[#666666]">→ Staff</span>
                    </div>
                    <div className="flex justify-between items-center">
                      <span className="text-[#B8B8B8]">mechanic@gmail.com</span>
                      <span className="text-[#666666]">→ Mechanic</span>
                    </div>
                    <div className="flex justify-between items-center">
                      <span className="text-[#B8B8B8]">admin@gmail.com</span>
                      <span className="text-[#666666]">→ Admin</span>
                    </div>
                    <div className="pt-2 mt-2 border-t border-white/10">
                      <span className="text-[#B8B8B8]">
                        Password for all: <strong className="text-[#E63946]">demo123</strong>
                      </span>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        {/* Back to Home */}
        <div className="mt-6 text-center">
          <Link
            to="/"
            className="text-[#B8B8B8] hover:text-white transition-colors inline-flex items-center gap-2"
          >
            ← Back to Home
          </Link>
        </div>
      </div>
    </div>
  );
}