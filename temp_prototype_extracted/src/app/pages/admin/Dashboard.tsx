import { DashboardLayout } from '../../components/DashboardLayout';
import { StatCard } from '../../components/Card';
import { DollarSign, Calendar, CheckCircle, Users } from 'lucide-react';
import { Card } from '../../components/Card';
import { BarChart, Bar, LineChart, Line, XAxis, YAxis, CartesianGrid, Tooltip, Legend, ResponsiveContainer, PieChart, Pie, Cell } from 'recharts';

export default function AdminDashboard() {
  // Monthly Services Data
  const monthlyServicesData = [
    { id: 'jan-svc', month: 'Jan', services: 45 },
    { id: 'feb-svc', month: 'Feb', services: 52 },
    { id: 'mar-svc', month: 'Mar', services: 61 },
    { id: 'apr-svc', month: 'Apr', services: 48 },
    { id: 'may-svc', month: 'May', services: 73 },
    { id: 'jun-svc', month: 'Jun', services: 68 },
  ];

  // Revenue Data
  const revenueData = [
    { id: 'jan-rev', month: 'Jan', revenue: 285000 },
    { id: 'feb-rev', month: 'Feb', revenue: 320000 },
    { id: 'mar-rev', month: 'Mar', revenue: 380000 },
    { id: 'apr-rev', month: 'Apr', revenue: 295000 },
    { id: 'may-rev', month: 'May', revenue: 450000 },
    { id: 'jun-rev', month: 'Jun', revenue: 410000 },
  ];

  // Service Distribution
  const serviceDistribution = [
    { id: 'dist-1', name: 'Engine Customization', value: 30 },
    { id: 'dist-2', name: 'Paint Job', value: 25 },
    { id: 'dist-3', name: 'Body Kit', value: 20 },
    { id: 'dist-4', name: 'Turbo Install', value: 15 },
    { id: 'dist-5', name: 'Exhaust', value: 10 },
  ];

  const COLORS = ['#E63946', '#457B9D', '#1F2937', '#F59E0B', '#10B981'];

  const recentBookings = [
    { id: 'bk-1', customer: 'Carlos Reyes', service: 'Body Kit Installation', date: 'April 8, 2026', status: 'pending' },
    { id: 'bk-2', customer: 'Ana Garcia', service: 'Exhaust Fabrication', date: 'April 10, 2026', status: 'pending' },
    { id: 'bk-3', customer: 'Juan Dela Cruz', service: 'Engine Customization', date: 'April 5, 2026', status: 'approved' },
  ];

  return (
    <DashboardLayout role="admin">
      <div className="space-y-8">
        {/* Header */}
        <div>
          <h1 className="text-3xl font-bold text-gray-900 dark:text-white mb-2">Admin Dashboard</h1>
          <p className="text-gray-600 dark:text-gray-400">System overview and analytics.</p>
        </div>

        {/* Stats */}
        <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
          <StatCard
            title="Total Bookings"
            value="248"
            icon={<Calendar size={24} />}
            color="blue"
            trend={{ value: '+12% from last month', isPositive: true }}
          />
          <StatCard
            title="Active Services"
            value="18"
            icon={<CheckCircle size={24} />}
            color="red"
            trend={{ value: '+5 this week', isPositive: true }}
          />
          <StatCard
            title="Completed Jobs"
            value="196"
            icon={<CheckCircle size={24} />}
            color="green"
            trend={{ value: '+18% completion rate', isPositive: true }}
          />
          <StatCard
            title="Monthly Revenue"
            value="₱410K"
            icon={<DollarSign size={24} />}
            color="charcoal"
            trend={{ value: '+8% from last month', isPositive: true }}
          />
        </div>

        {/* Charts Row 1 */}
        <div className="grid grid-cols-1 lg:grid-cols-2 gap-6">
          {/* Monthly Services Chart */}
          <Card>
            <h2 className="text-xl font-bold text-gray-900 dark:text-white mb-6">Monthly Services</h2>
            <ResponsiveContainer width="100%" height={300}>
              <BarChart data={monthlyServicesData}>
                <CartesianGrid key="dashboard-services-grid" strokeDasharray="3 3" stroke="#e5e7eb" className="dark:stroke-gray-700" />
                <XAxis key="dashboard-services-xaxis" dataKey="month" stroke="#6b7280" className="dark:stroke-gray-400" />
                <YAxis key="dashboard-services-yaxis" stroke="#6b7280" className="dark:stroke-gray-400" />
                <Tooltip key="dashboard-services-tooltip" contentStyle={{ backgroundColor: 'var(--color-card)', border: '1px solid var(--color-border)', borderRadius: '0.5rem' }} />
                <Legend key="dashboard-services-legend" />
                <Bar key="dashboard-services-bar" dataKey="services" fill="#E63946" radius={[8, 8, 0, 0]} />
              </BarChart>
            </ResponsiveContainer>
          </Card>

          {/* Revenue Chart */}
          <Card>
            <h2 className="text-xl font-bold text-gray-900 dark:text-white mb-6">Revenue Analytics (₱)</h2>
            <ResponsiveContainer width="100%" height={300}>
              <LineChart data={revenueData}>
                <CartesianGrid key="dashboard-revenue-grid" strokeDasharray="3 3" stroke="#e5e7eb" className="dark:stroke-gray-700" />
                <XAxis key="dashboard-revenue-xaxis" dataKey="month" stroke="#6b7280" className="dark:stroke-gray-400" />
                <YAxis key="dashboard-revenue-yaxis" stroke="#6b7280" className="dark:stroke-gray-400" />
                <Tooltip key="dashboard-revenue-tooltip" formatter={(value) => `₱${Number(value).toLocaleString()}`} contentStyle={{ backgroundColor: 'var(--color-card)', border: '1px solid var(--color-border)', borderRadius: '0.5rem' }} />
                <Legend key="dashboard-revenue-legend" />
                <Line key="dashboard-revenue-line" type="monotone" dataKey="revenue" stroke="#457B9D" strokeWidth={3} />
              </LineChart>
            </ResponsiveContainer>
          </Card>
        </div>

        {/* Charts Row 2 */}
        <div className="grid grid-cols-1 lg:grid-cols-2 gap-6">
          {/* Service Distribution */}
          <Card>
            <h2 className="text-xl font-bold text-gray-900 dark:text-white mb-6">Service Popularity</h2>
            <ResponsiveContainer width="100%" height={300}>
              <PieChart>
                <Pie
                  key="dashboard-service-pie"
                  data={serviceDistribution}
                  cx="50%"
                  cy="50%"
                  labelLine={false}
                  label={({ name, percent }) => `${name} ${(percent * 100).toFixed(0)}%`}
                  outerRadius={100}
                  fill="#8884d8"
                  dataKey="value"
                >
                  {serviceDistribution.map((entry, index) => (
                    <Cell key={entry.id} fill={COLORS[index % COLORS.length]} />
                  ))}
                </Pie>
                <Tooltip key="dashboard-service-tooltip" contentStyle={{ backgroundColor: 'var(--color-card)', border: '1px solid var(--color-border)', borderRadius: '0.5rem' }} />
              </PieChart>
            </ResponsiveContainer>
          </Card>

          {/* Recent Bookings */}
          <Card>
            <h2 className="text-xl font-bold text-gray-900 dark:text-white mb-6">Recent Booking Requests</h2>
            <div className="space-y-3">
              {recentBookings.map((booking) => (
                <div key={booking.id} className="flex items-center justify-between pb-3 border-b border-gray-200 dark:border-gray-700 last:border-0">
                  <div className="flex-1">
                    <p className="font-medium text-gray-900 dark:text-white">{booking.customer}</p>
                    <p className="text-sm text-gray-600 dark:text-gray-400">{booking.service}</p>
                    <p className="text-xs text-gray-500 dark:text-gray-500">{booking.date}</p>
                  </div>
                  <span className={`px-3 py-1 rounded-full text-xs font-medium ${
                    booking.status === 'approved' ? 'bg-green-100 dark:bg-green-500/20 text-green-800 dark:text-green-300' : 'bg-yellow-100 dark:bg-yellow-500/20 text-yellow-800 dark:text-yellow-300'
                  }`}>
                    {booking.status === 'approved' ? 'Approved' : 'Pending'}
                  </span>
                </div>
              ))}
            </div>
          </Card>
        </div>

        {/* Quick Stats Grid */}
        <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
          <Card className="text-center">
            <Users size={32} className="mx-auto mb-3 text-[#457B9D]" />
            <p className="text-sm text-gray-600 dark:text-gray-400 mb-1">Total Customers</p>
            <p className="text-2xl font-bold text-gray-900 dark:text-white">342</p>
          </Card>
          <Card className="text-center">
            <Users size={32} className="mx-auto mb-3 text-[#E63946]" />
            <p className="text-sm text-gray-600 dark:text-gray-400 mb-1">Active Mechanics</p>
            <p className="text-2xl font-bold text-gray-900 dark:text-white">8</p>
          </Card>
          <Card className="text-center">
            <Calendar size={32} className="mx-auto mb-3 text-green-600" />
            <p className="text-sm text-gray-600 dark:text-gray-400 mb-1">Today's Appointments</p>
            <p className="text-2xl font-bold text-gray-900 dark:text-white">6</p>
          </Card>
          <Card className="text-center">
            <CheckCircle size={32} className="mx-auto mb-3 text-gray-700 dark:text-gray-300" />
            <p className="text-sm text-gray-600 dark:text-gray-400 mb-1">Completion Rate</p>
            <p className="text-2xl font-bold text-gray-900 dark:text-white">94%</p>
          </Card>
        </div>
      </div>
    </DashboardLayout>
  );
}