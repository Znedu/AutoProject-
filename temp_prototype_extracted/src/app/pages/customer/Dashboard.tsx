import { DashboardLayout } from '../../components/DashboardLayout';
import { StatCard, Card } from '../../components/Card';
import { Calendar, Wrench, CheckCircle, MessageSquare, ChevronRight } from 'lucide-react';
import { Link } from 'react-router';
import { Button } from '../../components/Button';
import { StatusBadge } from '../../components/StatusBadge';
import { services, serviceCategories } from '../../data/services';

export default function CustomerDashboard() {
  const upcomingBookings = [
    {
      id: 1,
      service: 'Engine Customization',
      vehicle: 'Honda Civic 2020',
      date: 'April 5, 2026',
      status: 'confirmed' as const,
    },
  ];

  const activeServices = [
    {
      id: 1,
      service: 'Paint Job',
      vehicle: 'Toyota Supra 2021',
      progress: 65,
      status: 'Service Ongoing',
    },
  ];

  return (
    <DashboardLayout role="customer">
      <div className="space-y-8">
        {/* Header */}
        <div>
          <h1 className="text-3xl font-bold mb-2 text-gray-900 dark:text-white">Customer Dashboard</h1>
          <p className="text-gray-600 dark:text-gray-300">Welcome back! Here's your service overview.</p>
        </div>

        {/* Stats */}
        <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
          <StatCard
            title="Upcoming Bookings"
            value="1"
            icon={<Calendar size={24} />}
            color="blue"
          />
          <StatCard
            title="Active Services"
            value="1"
            icon={<Wrench size={24} />}
            color="red"
          />
          <StatCard
            title="Completed Services"
            value="5"
            icon={<CheckCircle size={24} />}
            color="green"
          />
          <StatCard
            title="Support Messages"
            value="2"
            icon={<MessageSquare size={24} />}
            color="charcoal"
          />
        </div>

        {/* Quick Actions */}
        <div className="rounded-lg p-6 bg-gray-100 dark:bg-[#151515]/80 backdrop-blur-md border border-gray-300 dark:border-[#E63946]/20">
          <h2 className="text-xl font-bold mb-4 text-gray-900 dark:text-white">Quick Actions</h2>
          <div className="flex flex-wrap gap-4">
            <Link className="rounded-[12px] bg-[#00aeff]" to="/customer/book-service">
              <Button className="text-[#ffffff]" variant="accent">Book New Service</Button>
            </Link>
            <Link className="bg-[#10101000] bg-[#00000000]" to="/customer/track">
              <Button className="text-[#282210] text-[#282210] text-[#40361a] text-[#514213] text-[#5d490d] text-[#674f06] text-[#6c5203] text-[#6d5203] text-[#6e5302] text-[#6f5401] text-[#735600] text-[#735700] text-[#745800] text-[#775a00] text-[#795b00] text-[#7c5e00] text-[#7e5f00] text-[#7f6000] text-[#806100] text-[#816200] text-[#826300] text-[#826300] text-[#846500] text-[#856600] text-[#836500] text-[#795d00] text-[#735800] text-[#715600] text-[#6c5300] text-[#695000] text-[#644d00] text-[#4f3d00] text-[#392b00] text-[#1f1800] text-[#141000] text-[#080600] text-[#000000] text-[#000000] text-[#000000] text-[#000000] text-[#000000] text-[#000000] text-[#000000] text-[#000000] text-[#000000] text-[#000000] text-[#000000] text-[#000000] text-[#000000] text-[#000000]" variant="secondary">Track Service</Button>
            </Link>
            <Link to="/customer/support">
              <Button variant="outline">Create Support Ticket</Button>
            </Link>
          </div>
        </div>

        {/* Available Services Overview */}
        <div className="rounded-lg p-6 bg-white dark:bg-[#151515]/60 backdrop-blur-md border border-gray-300 dark:border-gray-800 shadow-lg">
          <div className="flex items-center justify-between mb-6">
            <h2 className="text-xl font-bold text-gray-900 dark:text-white">Our Services</h2>
            <Link to="/customer/book-service">
              <Button variant="outline" size="sm">
                View All & Book
                <ChevronRight size={16} className="ml-1" />
              </Button>
            </Link>
          </div>
          
          <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            {serviceCategories.slice(0, 6).map((category) => {
              const categoryServices = services.filter((s) => s.category === category.id);
              const minPrice = Math.min(...categoryServices.map(s => s.estimatedPrice.min));
              const maxPrice = Math.max(...categoryServices.map(s => s.estimatedPrice.max));
              
              return (
                <Link 
                  key={category.id} 
                  to="/customer/book-service"
                  className="block"
                >
                  <div 
                    className="rounded-lg border-2 border-gray-300 dark:border-gray-700/50 hover:border-[#E63946] hover:shadow-xl hover:shadow-[#E63946]/20 transition-all group cursor-pointer h-full bg-gray-50 dark:bg-[#0B0B0B]/40 backdrop-blur-sm p-4 w-fit max-w-full"
                    style={{ 
                      borderLeftColor: (() => {
                        const colorMatch = category.color.match(/#[0-9A-Fa-f]{6}/);
                        return colorMatch ? colorMatch[0] : '#E63946';
                      })(),
                      borderLeftWidth: '4px' 
                    }}
                  >
                    <div className="flex items-start gap-3">
                      <div 
                        className="p-3 rounded-lg text-white flex-shrink-0 shadow-lg"
                        style={{ 
                          backgroundColor: (() => {
                            const colorMatch = category.color.match(/#[0-9A-Fa-f]{6}/);
                            return colorMatch ? colorMatch[0] : '#E63946';
                          })()
                        }}
                      >
                        <Wrench size={20} />
                      </div>
                      <div className="flex-1 min-w-0">
                        <h3 className="font-bold text-gray-900 dark:text-white mb-1 group-hover:text-[#E63946] transition-colors text-base">
                          {category.name}
                        </h3>
                        <p className="text-sm text-gray-600 dark:text-gray-300 mb-2 font-medium">
                          {categoryServices.length} services available
                        </p>
                        <p className="text-base font-bold text-[#E63946]">
                          ₱{minPrice.toLocaleString()} - ₱{maxPrice.toLocaleString()}
                        </p>
                      </div>
                    </div>
                  </div>
                </Link>
              );
            })}
          </div>
          
          <div className="mt-4 p-4 bg-red-50 dark:bg-[#E63946]/10 rounded-lg border border-red-200 dark:border-[#E63946]/30 backdrop-blur-sm">
            <p className="text-sm text-gray-700 dark:text-gray-300">
              <strong className="text-[#E63946]">Note:</strong> Prices shown are estimated ranges. Final pricing will be calculated after vehicle inspection. 
              We use quality branded parts and provide detailed quotes including all parts and labor costs.
            </p>
          </div>
        </div>

        {/* Upcoming Bookings */}
        <div className="rounded-lg p-6 bg-white dark:bg-[#151515]/60 backdrop-blur-md border border-gray-300 dark:border-gray-800 shadow-lg">
          <h2 className="text-xl font-bold text-gray-900 dark:text-white mb-4">Upcoming Bookings</h2>
          {upcomingBookings.length > 0 ? (
            <div className="space-y-4">
              {upcomingBookings.map((booking) => (
                <div key={booking.id} className="border border-gray-300 dark:border-gray-700 rounded-lg p-4 flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-gray-50 dark:bg-[#0B0B0B]/40 hover:bg-gray-100 dark:hover:bg-[#0B0B0B]/60 transition-colors">
                  <div className="flex-1">
                    <h3 className="font-bold text-gray-900 dark:text-white mb-1">{booking.service}</h3>
                    <p className="text-sm text-gray-600 dark:text-gray-400">{booking.vehicle}</p>
                    <p className="text-sm text-gray-600 dark:text-gray-400">{booking.date}</p>
                  </div>
                  <div className="flex items-center gap-3">
                    <StatusBadge status={booking.status}>Confirmed</StatusBadge>
                    <Link to="/customer/track">
                      <Button size="sm" variant="outline">View Details</Button>
                    </Link>
                  </div>
                </div>
              ))}
            </div>
          ) : (
            <p className="text-gray-600 dark:text-gray-400">No upcoming bookings</p>
          )}
        </div>

        {/* Active Services */}
        <div className="rounded-lg p-6 bg-white dark:bg-[#151515]/60 backdrop-blur-md border border-gray-300 dark:border-gray-800 shadow-lg">
          <h2 className="text-xl font-bold text-gray-900 dark:text-white mb-4">Active Services</h2>
          {activeServices.length > 0 ? (
            <div className="space-y-4">
              {activeServices.map((service) => (
                <div key={service.id} className="border border-gray-300 dark:border-gray-700 rounded-lg p-4 bg-gray-50 dark:bg-[#0B0B0B]/40">
                  <div className="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-3">
                    <div className="flex-1">
                      <h3 className="font-bold text-gray-900 dark:text-white mb-1">{service.service}</h3>
                      <p className="text-sm text-gray-600 dark:text-gray-400">{service.vehicle}</p>
                    </div>
                    <StatusBadge status="in-progress">{service.status}</StatusBadge>
                  </div>
                  <div className="space-y-2">
                    <div className="flex justify-between text-sm">
                      <span className="text-gray-600 dark:text-gray-400">Progress</span>
                      <span className="font-medium text-gray-900 dark:text-white">{service.progress}%</span>
                    </div>
                    <div className="w-full bg-gray-300 dark:bg-gray-800/50 rounded-full h-2">
                      <div
                        className="bg-gradient-to-r from-[#E63946] to-[#E63946]/80 h-2 rounded-full transition-all shadow-lg shadow-[#E63946]/30"
                        style={{ width: `${service.progress}%` }}
                      />
                    </div>
                  </div>
                </div>
              ))}
            </div>
          ) : (
            <p className="text-gray-600 dark:text-gray-400">No active services</p>
          )}
        </div>
      </div>
    </DashboardLayout>
  );
}