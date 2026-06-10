import { DashboardLayout } from '../../components/DashboardLayout';
import { Card } from '../../components/Card';
import { Button } from '../../components/Button';
import { 
  BarChart, Bar, LineChart, Line, XAxis, YAxis, CartesianGrid, 
  Tooltip, Legend, ResponsiveContainer, PieChart, Pie, Cell, AreaChart, Area 
} from 'recharts';
import { Download, Calendar, TrendingUp } from 'lucide-react';
import { showToast } from '../../utils/toast';

export default function Reports() {
  // Monthly Revenue Data
  const revenueData = [
    { id: 'jan-rev', month: 'Jan', revenue: 285000, bookings: 45 },
    { id: 'feb-rev', month: 'Feb', revenue: 320000, bookings: 52 },
    { id: 'mar-rev', month: 'Mar', revenue: 380000, bookings: 61 },
    { id: 'apr-rev', month: 'Apr', revenue: 295000, bookings: 48 },
    { id: 'may-rev', month: 'May', revenue: 450000, bookings: 73 },
    { id: 'jun-rev', month: 'Jun', revenue: 410000, bookings: 68 },
  ];

  // Service Popularity
  const servicePopularity = [
    { id: 'svc-1', name: 'Engine Custom.', bookings: 45, revenue: 3375000 },
    { id: 'svc-2', name: 'Paint Job', bookings: 38, revenue: 1900000 },
    { id: 'svc-3', name: 'Body Kit', bookings: 32, revenue: 1600000 },
    { id: 'svc-4', name: 'Turbo Install', bookings: 24, revenue: 2880000 },
    { id: 'svc-5', name: 'Exhaust', bookings: 29, revenue: 870000 },
  ];

  // Customer Activity
  const customerActivity = [
    { id: 'cust-jan', month: 'Jan', new: 28, returning: 17 },
    { id: 'cust-feb', month: 'Feb', new: 35, returning: 17 },
    { id: 'cust-mar', month: 'Mar', new: 42, returning: 19 },
    { id: 'cust-apr', month: 'Apr', new: 31, returning: 17 },
    { id: 'cust-may', month: 'May', new: 48, returning: 25 },
    { id: 'cust-jun', month: 'Jun', new: 45, returning: 23 },
  ];

  // Booking Trends by Status
  const bookingTrends = [
    { id: 'status-1', name: 'Completed', value: 196 },
    { id: 'status-2', name: 'In Progress', value: 18 },
    { id: 'status-3', name: 'Pending', value: 22 },
    { id: 'status-4', name: 'Cancelled', value: 12 },
  ];

  const COLORS = ['#10B981', '#457B9D', '#F59E0B', '#EF4444'];

  const handleExportReport = (reportType: string) => {
    showToast.success(`Exporting ${reportType} report...`);
  };

  return (
    <DashboardLayout role="admin">
      <div className="space-y-6">
        {/* Header */}
        <div className="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
          <div>
            <h1 className="text-3xl font-bold text-gray-900 dark:text-white mb-2">Reports & Analytics</h1>
            <p className="text-gray-600 dark:text-gray-400">Comprehensive business insights and performance metrics.</p>
          </div>
          <div className="flex gap-3">
            <Button variant="secondary" size="sm">
              <Calendar size={16} className="mr-2" />
              Date Range
            </Button>
            <Button variant="accent" size="sm">
              <Download size={16} className="mr-2" />
              Export All
            </Button>
          </div>
        </div>

        {/* Key Metrics */}
        <div className="grid grid-cols-1 md:grid-cols-4 gap-6">
          <Card className="bg-gradient-to-br from-[#E63946] to-[#D62839] text-white">
            <div className="flex items-start justify-between">
              <div>
                <p className="text-white/80 mb-2">Total Revenue</p>
                <p className="text-3xl font-bold">₱2.14M</p>
                <div className="flex items-center gap-2 mt-2">
                  <TrendingUp size={16} />
                  <span className="text-sm">+18% vs last period</span>
                </div>
              </div>
            </div>
          </Card>

          <Card className="bg-gradient-to-br from-[#457B9D] to-[#5A8FB0] text-white">
            <div>
              <p className="text-white/80 mb-2">Total Bookings</p>
              <p className="text-3xl font-bold">248</p>
              <div className="flex items-center gap-2 mt-2">
                <TrendingUp size={16} />
                <span className="text-sm">+12% vs last period</span>
              </div>
            </div>
          </Card>

          <Card className="bg-gradient-to-br from-green-500 to-green-600 text-white">
            <div>
              <p className="text-white/80 mb-2">Completion Rate</p>
              <p className="text-3xl font-bold">94%</p>
              <div className="flex items-center gap-2 mt-2">
                <TrendingUp size={16} />
                <span className="text-sm">+3% vs last period</span>
              </div>
            </div>
          </Card>

          <Card className="bg-gradient-to-br from-[#1F2937] to-[#374151] text-white">
            <div>
              <p className="text-white/80 mb-2">Avg. Service Value</p>
              <p className="text-3xl font-bold">₱8,630</p>
              <div className="flex items-center gap-2 mt-2">
                <TrendingUp size={16} />
                <span className="text-sm">+5% vs last period</span>
              </div>
            </div>
          </Card>
        </div>

        {/* Monthly Revenue & Bookings */}
        <Card>
          <div className="flex justify-between items-center mb-6">
            <h2 className="text-xl font-bold text-gray-900 dark:text-white">Monthly Revenue & Bookings</h2>
            <Button variant="ghost" size="sm" onClick={() => handleExportReport('Revenue')}>
              <Download size={16} className="mr-2" />
              Export
            </Button>
          </div>
          <ResponsiveContainer width="100%" height={350}>
            <AreaChart data={revenueData}>
              <defs>
                <linearGradient id="reportsColorRevenue" x1="0" y1="0" x2="0" y2="1">
                  <stop offset="5%" stopColor="#E63946" stopOpacity={0.8}/>
                  <stop offset="95%" stopColor="#E63946" stopOpacity={0}/>
                </linearGradient>
                <linearGradient id="reportsColorBookings" x1="0" y1="0" x2="0" y2="1">
                  <stop offset="5%" stopColor="#457B9D" stopOpacity={0.8}/>
                  <stop offset="95%" stopColor="#457B9D" stopOpacity={0}/>
                </linearGradient>
              </defs>
              <CartesianGrid key="reports-area-grid" strokeDasharray="3 3" />
              <XAxis key="reports-area-xaxis" dataKey="month" />
              <YAxis key="reports-area-yaxis-left" yAxisId="left" />
              <YAxis key="reports-area-yaxis-right" yAxisId="right" orientation="right" />
              <Tooltip key="reports-area-tooltip" formatter={(value, name) => {
                if (name === 'revenue') return `₱${Number(value).toLocaleString()}`;
                return value;
              }} />
              <Legend key="reports-area-legend" />
              <Area key="reports-revenue-area" yAxisId="left" type="monotone" dataKey="revenue" stroke="#E63946" fillOpacity={1} fill="url(#reportsColorRevenue)" />
              <Area key="reports-bookings-area" yAxisId="right" type="monotone" dataKey="bookings" stroke="#457B9D" fillOpacity={1} fill="url(#reportsColorBookings)" />
            </AreaChart>
          </ResponsiveContainer>
        </Card>

        {/* Service Popularity & Booking Status */}
        <div className="grid grid-cols-1 lg:grid-cols-2 gap-6">
          <Card>
            <div className="flex justify-between items-center mb-6">
              <h2 className="text-xl font-bold text-gray-900 dark:text-white">Service Popularity</h2>
              <Button variant="ghost" size="sm" onClick={() => handleExportReport('Services')}>
                <Download size={16} className="mr-2" />
                Export
              </Button>
            </div>
            <ResponsiveContainer width="100%" height={300}>
              <BarChart data={servicePopularity} layout="vertical">
                <CartesianGrid key="reports-service-grid" strokeDasharray="3 3" />
                <XAxis key="reports-service-xaxis" type="number" />
                <YAxis key="reports-service-yaxis" dataKey="name" type="category" width={100} />
                <Tooltip key="reports-service-tooltip" formatter={(value, name) => {
                  if (name === 'revenue') return `₱${Number(value).toLocaleString()}`;
                  return value;
                }} />
                <Legend key="reports-service-legend" />
                <Bar key="reports-service-bar" dataKey="bookings" fill="#E63946" radius={[0, 8, 8, 0]} />
              </BarChart>
            </ResponsiveContainer>
          </Card>

          <Card>
            <div className="flex justify-between items-center mb-6">
              <h2 className="text-xl font-bold text-gray-900 dark:text-white">Booking Status Distribution</h2>
              <Button variant="ghost" size="sm" onClick={() => handleExportReport('Booking Status')}>
                <Download size={16} className="mr-2" />
                Export
              </Button>
            </div>
            <ResponsiveContainer width="100%" height={300}>
              <PieChart>
                <Pie
                  key="reports-status-pie"
                  data={bookingTrends}
                  cx="50%"
                  cy="50%"
                  labelLine={false}
                  label={({ name, percent }) => `${name} ${(percent * 100).toFixed(0)}%`}
                  outerRadius={100}
                  fill="#8884d8"
                  dataKey="value"
                >
                  {bookingTrends.map((entry, index) => (
                    <Cell key={entry.id} fill={COLORS[index % COLORS.length]} />
                  ))}
                </Pie>
                <Tooltip key="reports-status-tooltip" />
              </PieChart>
            </ResponsiveContainer>
          </Card>
        </div>

        {/* Customer Activity */}
        <Card>
          <div className="flex justify-between items-center mb-6">
            <h2 className="text-xl font-bold text-gray-900 dark:text-white">Customer Activity Trends</h2>
            <Button variant="ghost" size="sm" onClick={() => handleExportReport('Customer Activity')}>
              <Download size={16} className="mr-2" />
              Export
            </Button>
          </div>
          <ResponsiveContainer width="100%" height={300}>
            <LineChart data={customerActivity}>
              <CartesianGrid key="reports-customer-grid" strokeDasharray="3 3" />
              <XAxis key="reports-customer-xaxis" dataKey="month" />
              <YAxis key="reports-customer-yaxis" />
              <Tooltip key="reports-customer-tooltip" />
              <Legend key="reports-customer-legend" />
              <Line key="reports-new-customers-line" type="monotone" dataKey="new" stroke="#E63946" strokeWidth={3} name="New Customers" />
              <Line key="reports-returning-customers-line" type="monotone" dataKey="returning" stroke="#457B9D" strokeWidth={3} name="Returning Customers" />
            </LineChart>
          </ResponsiveContainer>
        </Card>

        {/* Service Performance Table */}
        <Card>
          <div className="flex justify-between items-center mb-6">
            <h2 className="text-xl font-bold text-gray-900 dark:text-white">Service Performance Summary</h2>
            <Button variant="ghost" size="sm" onClick={() => handleExportReport('Service Performance')}>
              <Download size={16} className="mr-2" />
              Export
            </Button>
          </div>
          <div className="overflow-x-auto">
            <table className="w-full">
              <thead>
                <tr className="border-b border-gray-200 dark:border-gray-700">
                  <th className="text-left py-3 px-4 font-medium text-gray-600 dark:text-gray-400">Service</th>
                  <th className="text-left py-3 px-4 font-medium text-gray-600 dark:text-gray-400">Bookings</th>
                  <th className="text-left py-3 px-4 font-medium text-gray-600 dark:text-gray-400">Revenue</th>
                  <th className="text-left py-3 px-4 font-medium text-gray-600 dark:text-gray-400">Avg. Value</th>
                  <th className="text-left py-3 px-4 font-medium text-gray-600 dark:text-gray-400">Trend</th>
                </tr>
              </thead>
              <tbody>
                {servicePopularity.map((service) => (
                  <tr key={service.id} className="border-b border-gray-100 dark:border-gray-800 hover:bg-gray-50 dark:hover:bg-white/5">
                    <td className="py-3 px-4 font-medium text-gray-900 dark:text-white">{service.name}</td>
                    <td className="py-3 px-4 text-gray-600 dark:text-gray-400">{service.bookings}</td>
                    <td className="py-3 px-4 text-gray-600 dark:text-gray-400">₱{service.revenue.toLocaleString()}</td>
                    <td className="py-3 px-4 text-gray-600 dark:text-gray-400">₱{(service.revenue / service.bookings).toLocaleString(undefined, {maximumFractionDigits: 0})}</td>
                    <td className="py-3 px-4">
                      <span className="text-green-600 dark:text-green-500 flex items-center gap-1">
                        <TrendingUp size={16} />
                        +{Math.floor(Math.random() * 20 + 5)}%
                      </span>
                    </td>
                  </tr>
                ))}
              </tbody>
            </table>
          </div>
        </Card>
      </div>
    </DashboardLayout>
  );
}