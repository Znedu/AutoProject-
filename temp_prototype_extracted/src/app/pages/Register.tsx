import { useState } from 'react';
import { Link, useNavigate } from 'react-router';
import { Button } from '../components/Button';
import { Input } from '../components/FormInputs';
import { showToast } from '../utils/toast';

export default function Register() {
  const [formData, setFormData] = useState({
    fullName: '',
    email: '',
    phone: '',
    password: '',
    confirmPassword: '',
  });
  const navigate = useNavigate();

  const handleChange = (e: React.ChangeEvent<HTMLInputElement>) => {
    setFormData({
      ...formData,
      [e.target.name]: e.target.value,
    });
  };

  const handleRegister = (e: React.FormEvent) => {
    e.preventDefault();
    
    if (formData.password !== formData.confirmPassword) {
      showToast.error('Passwords do not match!');
      return;
    }
    
    showToast.success('Account created successfully!');
    // Simulate registration - redirect to customer dashboard
    navigate('/customer');
  };

  return (
    <div className="min-h-screen bg-gradient-to-br from-[#1F2937] via-[#374151] to-[#1F2937] flex items-center justify-center p-4">
      <div className="w-full max-w-md">
        {/* Logo */}
        <div className="text-center mb-8">
          <h1 className="text-4xl font-bold text-white mb-2">AutoProject+</h1>
          <p className="text-gray-300">Create your account and get started.</p>
        </div>

        {/* Register Card */}
        <div className="bg-white rounded-lg shadow-xl p-8">
          <h2 className="text-2xl font-bold text-[#1F2937] mb-6">Create Account</h2>

          <form onSubmit={handleRegister} className="space-y-4">
            <Input
              label="Full Name"
              type="text"
              name="fullName"
              placeholder="John Doe"
              value={formData.fullName}
              onChange={handleChange}
              required
            />

            <Input
              label="Email Address"
              type="email"
              name="email"
              placeholder="your@email.com"
              value={formData.email}
              onChange={handleChange}
              required
            />

            <Input
              label="Phone Number"
              type="tel"
              name="phone"
              placeholder="+63 912 345 6789"
              value={formData.phone}
              onChange={handleChange}
              required
            />

            <Input
              label="Password"
              type="password"
              name="password"
              placeholder="Create a strong password"
              value={formData.password}
              onChange={handleChange}
              required
            />

            <Input
              label="Confirm Password"
              type="password"
              name="confirmPassword"
              placeholder="Re-enter your password"
              value={formData.confirmPassword}
              onChange={handleChange}
              required
            />

            <div className="flex items-start gap-2">
              <input type="checkbox" required className="mt-1" />
              <label className="text-sm text-gray-600">
                I agree to the Terms of Service and Privacy Policy
              </label>
            </div>

            <Button type="submit" variant="accent" className="w-full" size="lg">
              Register
            </Button>
          </form>

          <div className="mt-6 text-center">
            <p className="text-gray-600">
              Already have an account?{' '}
              <Link to="/login" className="text-[#E63946] hover:underline font-medium">
                Login here
              </Link>
            </p>
          </div>
        </div>

        {/* Back to Home */}
        <div className="text-center mt-6">
          <Link to="/" className="text-white hover:text-gray-300 transition-colors">
            ← Back to Home
          </Link>
        </div>
      </div>
    </div>
  );
}