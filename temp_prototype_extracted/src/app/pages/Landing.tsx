import { Link } from 'react-router';
import { Button } from '../components/Button';
import {
  Calendar,
  DollarSign,
  MapPin,
  FileText,
  MessageCircle,
  CheckCircle,
  Wrench,
  Gauge,
  Settings,
  Sparkles,
  ChevronRight,
  ArrowRight,
} from 'lucide-react';

export default function Landing() {
  const features = [
    {
      icon: <Calendar size={40} />,
      title: 'Online Booking System',
      description: 'Book your service appointments online 24/7 with real-time slot availability',
    },
    {
      icon: <DollarSign size={40} />,
      title: 'Automated Cost Estimation',
      description: 'Get instant cost estimates with brand-specific pricing for transparency',
    },
    {
      icon: <MapPin size={40} />,
      title: 'Service Tracking Dashboard',
      description: 'Track your vehicle service progress in real-time with live updates',
    },
  ];

  const services = [
    {
      title: 'Customization Services',
      description: 'Body kits, paint jobs, wraps, and performance modifications',
      image: 'https://images.unsplash.com/photo-1570762574066-a238075b62f9?crop=entropy&cs=tinysrgb&fit=max&fm=jpg&q=80&w=1080',
      icon: <Wrench size={32} />,
    },
    {
      title: 'Maintenance Services',
      description: 'Oil changes, brake service, engine tune-ups, and routine maintenance',
      image: 'https://images.unsplash.com/photo-1759189196663-209ff7cda669?crop=entropy&cs=tinysrgb&fit=max&fm=jpg&q=80&w=1080',
      icon: <Settings size={32} />,
    },
    {
      title: 'Diagnostics & Inspection',
      description: 'Computer diagnostics, pre-purchase inspection, and system checks',
      image: 'https://images.unsplash.com/photo-1664530550244-d616a32ed041?crop=entropy&cs=tinysrgb&fit=max&fm=jpg&q=80&w=1080',
      icon: <Gauge size={32} />,
    },
    {
      title: 'Performance Upgrades',
      description: 'Turbo installation, exhaust systems, ECU tuning, and suspension',
      image: 'https://images.unsplash.com/photo-1768387666438-b3da75373846?crop=entropy&cs=tinysrgb&fit=max&fm=jpg&q=80&w=1080',
      icon: <Gauge size={32} />,
    },
    {
      title: 'Interior Customization',
      description: 'Upholstery, sound systems, ambient lighting, and trim upgrades',
      image: 'https://images.unsplash.com/photo-1664530550244-d616a32ed041?crop=entropy&cs=tinysrgb&fit=max&fm=jpg&q=80&w=1080',
      icon: <Sparkles size={32} />,
    },
    {
      title: 'Detailing Services',
      description: 'Ceramic coating, paint protection film, and professional detailing',
      image: 'https://images.unsplash.com/photo-1763087978864-fe5b2778c9f7?crop=entropy&cs=tinysrgb&fit=max&fm=jpg&q=80&w=1080',
      icon: <Sparkles size={32} />,
    },
  ];

  return (
    <div className="min-h-screen bg-[#0B0B0B]">
      {/* Navigation */}
      <nav className="fixed w-full top-0 z-50 glass-card border-b border-white/10">
        <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
          <div className="flex justify-between items-center h-20">
            <div className="flex items-center">
              <h1 className="text-2xl font-bold text-white tracking-wider">
                AUTO<span className="text-[#E63946]">PROJECT</span>+
              </h1>
            </div>
            <div className="hidden md:flex items-center gap-8">
              <a
                href="#home"
                className="text-[#B8B8B8] hover:text-white transition-colors duration-300"
              >
                Home
              </a>
              <a
                href="#services"
                className="text-[#B8B8B8] hover:text-white transition-colors duration-300"
              >
                Services
              </a>
              <a
                href="#features"
                className="text-[#B8B8B8] hover:text-white transition-colors duration-300"
              >
                Features
              </a>
              <a
                href="#about"
                className="text-[#B8B8B8] hover:text-white transition-colors duration-300"
              >
                About
              </a>
            </div>
            <div className="flex items-center gap-4">
              <Link to="/login">
                <button className="px-6 py-2.5 text-white border border-white/20 rounded-lg hover:border-[#E63946] hover:text-[#E63946] transition-all duration-300">
                  Login
                </button>
              </Link>
              <Link to="/register">
                <button className="px-6 py-2.5 bg-gradient-red text-white rounded-lg hover:shadow-lg hover:shadow-[#E63946]/50 transition-all duration-300 glow-red-hover">
                  Get Started
                </button>
              </Link>
            </div>
          </div>
        </div>
      </nav>

      {/* Hero Section */}
      <section
        id="home"
        className="relative min-h-screen flex items-center justify-center overflow-hidden pt-20"
        style={{
          backgroundImage: `url('https://images.unsplash.com/photo-1763087978864-fe5b2778c9f7?crop=entropy&cs=tinysrgb&fit=max&fm=jpg&q=80&w=1920')`,
          backgroundSize: 'cover',
          backgroundPosition: 'center',
          backgroundAttachment: 'fixed',
        }}
      >
        {/* Dark Overlay */}
        <div className="absolute inset-0 bg-gradient-to-b from-black/80 via-black/70 to-black/90"></div>

        {/* Content */}
        <div className="relative z-10 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
          <div className="max-w-4xl mx-auto">
            <h1 className="text-5xl sm:text-6xl lg:text-7xl font-bold text-white mb-6 tracking-tight">
              Smart Automotive
              <br />
              <span className="text-glow text-[#E63946]">Service Management</span>
            </h1>
            <p className="text-xl sm:text-2xl text-[#B8B8B8] mb-12 max-w-3xl mx-auto leading-relaxed">
              Manage bookings, customization services, and vehicle maintenance efficiently with
              <span className="text-white font-semibold"> AutoProject+</span>
            </p>
            <div className="flex flex-col sm:flex-row gap-6 justify-center">
              <Link to="/register">
                <button className="group px-10 py-4 bg-gradient-red text-white rounded-xl text-lg font-semibold hover:shadow-2xl hover:shadow-[#E63946]/50 transition-all duration-300 glow-red flex items-center justify-center gap-3">
                  Book a Service
                  <ArrowRight
                    size={20}
                    className="group-hover:translate-x-1 transition-transform"
                  />
                </button>
              </Link>
              <a href="#services">
                <button className="px-10 py-4 bg-white/10 backdrop-blur-sm text-white rounded-xl text-lg font-semibold border border-white/20 hover:bg-white/20 hover:border-[#E63946] transition-all duration-300">
                  Explore Services
                </button>
              </a>
            </div>
          </div>

          {/* Scroll Indicator */}
          <div className="absolute bottom-10 left-1/2 transform -translate-x-1/2 animate-bounce">
            <ChevronRight size={32} className="text-white/50 rotate-90" />
          </div>
        </div>
      </section>

      {/* Feature Highlights Section */}
      <section id="features" className="py-24 bg-gradient-to-b from-[#0B0B0B] to-[#151515]">
        <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
          <div className="text-center mb-16">
            <h2 className="text-4xl lg:text-5xl font-bold text-white mb-4">
              Platform Features
            </h2>
            <p className="text-lg text-[#B8B8B8] max-w-2xl mx-auto">
              Modern features designed for seamless automotive service management
            </p>
          </div>

          <div className="grid grid-cols-1 md:grid-cols-3 gap-8">
            {features.map((feature, index) => (
              <div
                key={index}
                className="glass-card glass-hover p-8 rounded-2xl group cursor-pointer"
              >
                <div className="mb-6 text-[#E63946] group-hover:scale-110 transition-transform duration-300">
                  {feature.icon}
                </div>
                <h3 className="text-2xl font-bold text-white mb-4">{feature.title}</h3>
                <p className="text-[#B8B8B8] leading-relaxed">{feature.description}</p>
              </div>
            ))}
          </div>
        </div>
      </section>

      {/* Service Categories Section */}
      <section id="services" className="py-24 bg-[#151515]">
        <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
          <div className="text-center mb-16">
            <h2 className="text-4xl lg:text-5xl font-bold text-white mb-4">
              Our Garage Services
            </h2>
            <p className="text-lg text-[#B8B8B8] max-w-2xl mx-auto">
              Comprehensive automotive customization and maintenance services
            </p>
          </div>

          <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
            {services.map((service, index) => (
              <div
                key={index}
                className="group relative overflow-hidden rounded-2xl glass-card glass-hover cursor-pointer h-80"
              >
                {/* Background Image */}
                <div
                  className="absolute inset-0 bg-cover bg-center transition-transform duration-500 group-hover:scale-110"
                  style={{
                    backgroundImage: `url('${service.image}')`,
                  }}
                ></div>

                {/* Gradient Overlay */}
                <div className="absolute inset-0 bg-gradient-to-t from-black via-black/80 to-transparent"></div>

                {/* Content */}
                <div className="relative h-full flex flex-col justify-end p-6">
                  <div className="mb-4 text-[#E63946] group-hover:scale-110 transition-transform duration-300">
                    {service.icon}
                  </div>
                  <h3 className="text-2xl font-bold text-white mb-3">{service.title}</h3>
                  <p className="text-[#B8B8B8] mb-4">{service.description}</p>
                  <button className="flex items-center gap-2 text-[#E63946] font-semibold group-hover:gap-4 transition-all duration-300">
                    Learn More
                    <ChevronRight size={20} />
                  </button>
                </div>
              </div>
            ))}
          </div>
        </div>
      </section>

      {/* About Section */}
      <section id="about" className="py-24 bg-gradient-to-b from-[#151515] to-[#0B0B0B]">
        <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
          <div className="grid grid-cols-1 lg:grid-cols-2 gap-12 items-center">
            <div>
              <h2 className="text-4xl lg:text-5xl font-bold text-white mb-6">
                About AutoProject-D Custom Garage
              </h2>
              <p className="text-lg text-[#B8B8B8] mb-6 leading-relaxed">
                We are a premium automotive customization and service center specializing in
                performance upgrades, aesthetic modifications, and professional maintenance services.
              </p>
              <p className="text-lg text-[#B8B8B8] mb-8 leading-relaxed">
                With AutoProject+, we bring transparency, efficiency, and modern technology to
                automotive service management. Track your bookings, monitor service progress, and
                communicate with our team - all from one platform.
              </p>
              <div className="grid grid-cols-2 gap-6">
                <div className="glass-card p-6 rounded-xl">
                  <div className="text-3xl font-bold text-[#E63946] mb-2">500+</div>
                  <div className="text-[#B8B8B8]">Completed Projects</div>
                </div>
                <div className="glass-card p-6 rounded-xl">
                  <div className="text-3xl font-bold text-[#E63946] mb-2">98%</div>
                  <div className="text-[#B8B8B8]">Customer Satisfaction</div>
                </div>
              </div>
            </div>
            <div
              className="relative h-96 lg:h-full rounded-2xl overflow-hidden glass-card"
              style={{
                backgroundImage: `url('https://images.unsplash.com/photo-1759189196663-209ff7cda669?crop=entropy&cs=tinysrgb&fit=max&fm=jpg&q=80&w=1080')`,
                backgroundSize: 'cover',
                backgroundPosition: 'center',
              }}
            >
              <div className="absolute inset-0 bg-gradient-to-t from-black/60 to-transparent"></div>
            </div>
          </div>
        </div>
      </section>

      {/* CTA Section */}
      <section className="py-24 bg-[#151515] relative overflow-hidden">
        <div className="absolute inset-0 opacity-10">
          <div className="absolute top-1/2 left-1/4 w-96 h-96 bg-[#E63946] rounded-full blur-3xl"></div>
          <div className="absolute bottom-1/4 right-1/4 w-96 h-96 bg-[#E63946] rounded-full blur-3xl"></div>
        </div>
        <div className="relative z-10 max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
          <h2 className="text-4xl lg:text-5xl font-bold text-white mb-6">
            Ready to Transform Your Vehicle?
          </h2>
          <p className="text-xl text-[#B8B8B8] mb-10">
            Join AutoProject+ today and experience the future of automotive service management
          </p>
          <Link to="/register">
            <button className="px-12 py-5 bg-gradient-red text-white rounded-xl text-lg font-semibold hover:shadow-2xl hover:shadow-[#E63946]/50 transition-all duration-300 glow-red">
              Create Your Account
            </button>
          </Link>
        </div>
      </section>

      {/* Footer */}
      <footer className="bg-[#0B0B0B] border-t border-white/10 py-12">
        <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
          <div className="grid grid-cols-1 md:grid-cols-4 gap-8 mb-8">
            <div>
              <h3 className="text-xl font-bold text-white mb-4">
                AUTO<span className="text-[#E63946]">PROJECT</span>+
              </h3>
              <p className="text-[#B8B8B8] text-sm">
                Modern automotive service management platform for AutoProject-D Custom Garage
              </p>
            </div>
            <div>
              <h4 className="text-white font-semibold mb-4">Services</h4>
              <ul className="space-y-2 text-[#B8B8B8] text-sm">
                <li>Customization</li>
                <li>Maintenance</li>
                <li>Diagnostics</li>
                <li>Performance</li>
              </ul>
            </div>
            <div>
              <h4 className="text-white font-semibold mb-4">Platform</h4>
              <ul className="space-y-2 text-[#B8B8B8] text-sm">
                <li>Booking</li>
                <li>Tracking</li>
                <li>Support</li>
                <li>Reports</li>
              </ul>
            </div>
            <div>
              <h4 className="text-white font-semibold mb-4">Contact</h4>
              <ul className="space-y-2 text-[#B8B8B8] text-sm">
                <li>support@autoproject.com</li>
                <li>+63 912 345 6789</li>
                <li>Manila, Philippines</li>
              </ul>
            </div>
          </div>
          <div className="border-t border-white/10 pt-8 text-center text-[#B8B8B8] text-sm">
            <p>© 2026 AutoProject+. All rights reserved. AutoProject-D Custom Garage.</p>
          </div>
        </div>
      </footer>
    </div>
  );
}
