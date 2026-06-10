import { useState, useMemo } from 'react';
import { useNavigate } from 'react-router';
import { DashboardLayout } from '../../components/DashboardLayout';
import { Card } from '../../components/Card';
import { Button } from '../../components/Button';
import { Input, TextArea } from '../../components/FormInputs';
import {
  Calendar,
  DollarSign,
  AlertCircle,
  Smartphone,
  CheckCircle2,
  Info,
  Wrench,
  ChevronDown,
  ChevronUp,
  Package,
  Tag,
  ArrowLeft,
  ArrowRight,
  Clock,
  X,
} from 'lucide-react';
import { showToast } from '../../utils/toast';
import { services, serviceCategories } from '../../data/services';
import gcashQRCode from 'figma:asset/d9623627c14fd6ee3776a1e17ca6d914656741c0.png';

export default function BookService() {
  const navigate = useNavigate();
  const [currentStep, setCurrentStep] = useState(1); // 1 = Service Selection, 2 = Booking Details, 3 = Payment & Terms
  const [formData, setFormData] = useState({
    customerName: '',
    contactNumber: '',
    vehicleMake: '',
    vehicleModel: '',
    vehicleYear: '',
    plateNumber: '',
    preferredDate: '',
    preferredTime: '',
    notes: '',
  });

  const [selectedServices, setSelectedServices] = useState<string[]>([]);
  const [selectedBrands, setSelectedBrands] = useState<Record<string, string>>({});
  const [expandedCategories, setExpandedCategories] = useState<string[]>(['exterior', 'performance']);
  const [paymentMethod, setPaymentMethod] = useState<'gcash' | 'maya' | ''>('');
  const [referenceNumber, setReferenceNumber] = useState('');
  const [agreedToTerms, setAgreedToTerms] = useState(false);
  const [showTerms, setShowTerms] = useState(false);
  const [selectedTimeSlot, setSelectedTimeSlot] = useState('');

  // Available time slots (8 AM to 5 PM)
  const timeSlots = [
    '08:00 AM',
    '09:00 AM',
    '10:00 AM',
    '11:00 AM',
    '12:00 PM',
    '01:00 PM',
    '02:00 PM',
    '03:00 PM',
    '04:00 PM',
    '05:00 PM',
  ];

  // Mock booked appointments - in a real app, this would come from the backend
  const bookedAppointments = useMemo(() => ({
    '2026-04-10': ['09:00 AM', '10:00 AM', '02:00 PM'],
    '2026-04-11': ['08:00 AM', '11:00 AM', '03:00 PM', '04:00 PM'],
    '2026-04-12': ['10:00 AM', '01:00 PM'],
    '2026-04-14': ['09:00 AM', '12:00 PM', '02:00 PM', '05:00 PM'],
    '2026-04-15': ['08:00 AM', '09:00 AM', '10:00 AM', '11:00 AM', '12:00 PM'],
  }), []);

  // Get unavailable dates (fully booked or closed)
  const getDateAvailability = (date: string) => {
    const bookedSlots = bookedAppointments[date] || [];
    const availableSlots = timeSlots.filter(slot => !bookedSlots.includes(slot));
    return {
      isFullyBooked: availableSlots.length === 0,
      availableSlots,
      bookedSlots,
    };
  };

  // Check if a date is in the past or today
  const isDateDisabled = (dateStr: string) => {
    const today = new Date();
    today.setHours(0, 0, 0, 0);
    const checkDate = new Date(dateStr);
    return checkDate < today;
  };

  // Check if a date is a Sunday (shop closed)
  const isSunday = (dateStr: string) => {
    const date = new Date(dateStr);
    return date.getDay() === 0;
  };

  const handleChange = (e: React.ChangeEvent<HTMLInputElement | HTMLTextAreaElement>) => {
    const { name, value } = e.target;
    setFormData({
      ...formData,
      [name]: value,
    });
  };

  const toggleService = (serviceId: string) => {
    setSelectedServices((prev) =>
      prev.includes(serviceId)
        ? prev.filter((id) => id !== serviceId)
        : [...prev, serviceId]
    );
    // Remove brand selection if service is deselected
    if (selectedServices.includes(serviceId)) {
      const newBrands = { ...selectedBrands };
      delete newBrands[serviceId];
      setSelectedBrands(newBrands);
    }
  };

  const handleBrandSelection = (serviceId: string, brand: string) => {
    setSelectedBrands((prev) => ({
      ...prev,
      [serviceId]: brand,
    }));
  };

  const toggleCategory = (categoryId: string) => {
    setExpandedCategories((prev) =>
      prev.includes(categoryId)
        ? prev.filter((id) => id !== categoryId)
        : [...prev, categoryId]
    );
  };

  // Calculate estimated cost based on selected services
  const estimatedCost = useMemo(() => {
    if (selectedServices.length === 0) return null;

    const selectedServiceData = services.filter((s) => selectedServices.includes(s.id));
    const minTotal = selectedServiceData.reduce((sum, s) => sum + s.estimatedPrice.min, 0);
    const maxTotal = selectedServiceData.reduce((sum, s) => sum + s.estimatedPrice.max, 0);

    return {
      min: minTotal,
      max: maxTotal,
      display: `₱${minTotal.toLocaleString()} - ₱${maxTotal.toLocaleString()}`,
    };
  }, [selectedServices]);

  const handleProceedToDetails = () => {
    // Validate selected services before proceeding
    if (selectedServices.length === 0) {
      showToast.error('Please select at least one service');
      return;
    }
    setCurrentStep(2);
    window.scrollTo(0, 0);
  };

  const handleProceedToPayment = (e: React.FormEvent) => {
    e.preventDefault();

    // Check if selected date is a Sunday
    if (isSunday(formData.preferredDate)) {
      showToast.error('We are closed on Sundays. Please select a weekday (Monday-Saturday).');
      return;
    }

    // Check if selected date is fully booked
    const availability = getDateAvailability(formData.preferredDate);
    if (availability.isFullyBooked) {
      showToast.error('Selected date is fully booked. Please choose another date.');
      return;
    }

    // Validate time slot selection
    if (!selectedTimeSlot || !formData.preferredTime) {
      showToast.error('Please select a time slot for your appointment');
      return;
    }

    setCurrentStep(3);
    window.scrollTo(0, 0);
  };

  const handleBackToServices = () => {
    setCurrentStep(1);
    window.scrollTo(0, 0);
  };

  const handleBackToDetails = () => {
    setCurrentStep(2);
    window.scrollTo(0, 0);
  };

  const handleSubmit = (e: React.FormEvent) => {
    e.preventDefault();

    // Validate reservation fee payment
    if (!paymentMethod) {
      showToast.error('Please select a payment method for the reservation fee');
      return;
    }

    if (!referenceNumber.trim()) {
      showToast.error('Please enter your payment reference number');
      return;
    }

    if (!agreedToTerms) {
      showToast.error('Please agree to the terms and conditions');
      return;
    }

    // Simulate booking submission
    showToast.success(
      'Booking submitted successfully! Admin will verify your payment and confirm your booking.'
    );
    navigate('/customer/bookings');
  };

  return (
    <DashboardLayout role="customer">
      <div className="max-w-7xl mx-auto space-y-6">
        {/* Progress Indicator */}
        <div className="flex items-center justify-center gap-3 mb-6">
          <div className="flex items-center gap-2">
            <div className={`w-10 h-10 rounded-full flex items-center justify-center font-bold ${
              currentStep === 1 ? 'bg-[#E63946] text-white' : 'bg-green-500 text-white'
            }`}>
              {currentStep === 1 ? '1' : <CheckCircle2 size={20} />}
            </div>
            <span className={`font-medium text-sm ${currentStep === 1 ? 'text-gray-900 dark:text-white' : 'text-gray-600 dark:text-gray-400'}`}>
              Services
            </span>
          </div>
          <div className="w-12 h-1 bg-gray-300 dark:bg-gray-700"></div>
          <div className="flex items-center gap-2">
            <div className={`w-10 h-10 rounded-full flex items-center justify-center font-bold ${
              currentStep === 2 ? 'bg-[#E63946] text-white' : currentStep > 2 ? 'bg-green-500 text-white' : 'bg-gray-300 dark:bg-gray-700 text-gray-600 dark:text-gray-400'
            }`}>
              {currentStep > 2 ? <CheckCircle2 size={20} /> : '2'}
            </div>
            <span className={`font-medium text-sm ${currentStep === 2 ? 'text-gray-900 dark:text-white' : 'text-gray-600 dark:text-gray-400'}`}>
              Details
            </span>
          </div>
          <div className="w-12 h-1 bg-gray-300 dark:bg-gray-700"></div>
          <div className="flex items-center gap-2">
            <div className={`w-10 h-10 rounded-full flex items-center justify-center font-bold ${
              currentStep === 3 ? 'bg-[#E63946] text-white' : 'bg-gray-300 dark:bg-gray-700 text-gray-600 dark:text-gray-400'
            }`}>
              3
            </div>
            <span className={`font-medium text-sm ${currentStep === 3 ? 'text-gray-900 dark:text-white' : 'text-gray-600 dark:text-gray-400'}`}>
              Payment
            </span>
          </div>
        </div>

        {/* Header */}
        <div>
          <h1 className="text-3xl font-bold mb-2 text-gray-900 dark:text-white">
            {currentStep === 1 ? 'Select Your Services' : currentStep === 2 ? 'Booking Information' : 'Payment & Confirmation'}
          </h1>
          <p className="text-gray-700 dark:text-gray-300">
            {currentStep === 1
              ? 'Choose the services you need for your vehicle.'
              : currentStep === 2
              ? 'Fill in your customer, vehicle, and appointment details.'
              : 'Review terms and complete the ₱200 reservation fee payment.'
            }
          </p>
        </div>

        {/* STEP 1: Service Selection */}
        {currentStep === 1 && (
          <>
            {/* Pricing Disclaimer */}
            <Card className="bg-amber-50 dark:bg-amber-950/30 border-l-4 border-amber-500">
              <div className="flex gap-3">
                <Package className="text-amber-600 dark:text-amber-500 flex-shrink-0" size={24} />
                <div className="text-sm">
                  <p className="font-medium mb-2 text-gray-900 dark:text-white">Pricing Information</p>
                  <ul className="space-y-1 list-disc list-inside text-gray-700 dark:text-gray-300">
                    <li>
                      <strong>Estimated prices</strong> shown below are for reference only.
                    </li>
                    <li>
                      <strong>Final pricing</strong> will be manually calculated by our staff after
                      vehicle inspection.
                    </li>
                    <li>
                      <strong>AutoProject-D Custom Garage</strong> uses branded, quality parts and
                      materials.
                    </li>
                    <li>
                      The <strong>brand and exact price</strong> of parts used will be provided in your
                      detailed quote.
                    </li>
                  </ul>
                </div>
              </div>
            </Card>
          {/* Service Selection */}
          <Card>
            <div className="flex items-center gap-2 mb-4">
              <Wrench className="text-[#E63946]" size={24} />
              <h2 className="text-xl font-bold text-gray-900 dark:text-white">
                Select Services ({selectedServices.length} selected)
              </h2>
            </div>

            <p className="text-sm mb-4 text-gray-700 dark:text-gray-300">
              Choose one or multiple services that you need. You can select services from different
              categories.
            </p>

            <div className="space-y-4">
              {serviceCategories.map((category) => {
                const categoryServices = services.filter((s) => s.category === category.id);
                const isExpanded = expandedCategories.includes(category.id);

                return (
                  <div
                    key={category.id}
                    className="border-2 border-gray-200 dark:border-white/10 rounded-lg overflow-hidden"
                  >
                    {/* Category Header */}
                    <button
                      type="button"
                      onClick={() => toggleCategory(category.id)}
                      className="w-full flex items-center justify-between p-4 hover:bg-gray-100 dark:hover:bg-white/5 transition-colors"
                      style={{ borderLeftColor: category.color, borderLeftWidth: '4px' }}
                    >
                      <div className="flex items-center gap-3">
                        <div
                          className="p-2 rounded-lg text-white"
                          style={{ backgroundColor: category.color }}
                        >
                          <Wrench size={20} />
                        </div>
                        <div className="text-left">
                          <h3 className="font-bold text-gray-900 dark:text-white">{category.name}</h3>
                          <p className="text-xs text-gray-600 dark:text-gray-400">{categoryServices.length} services available</p>
                        </div>
                      </div>
                      {isExpanded ? (
                        <ChevronUp className="text-gray-400" size={20} />
                      ) : (
                        <ChevronDown className="text-gray-400" size={20} />
                      )}
                    </button>

                    {/* Category Services */}
                    {isExpanded && (
                      <div className="p-4 bg-white dark:bg-[#0B0B0B] grid grid-cols-1 md:grid-cols-2 gap-3">
                        {categoryServices.map((service) => {
                          const isSelected = selectedServices.includes(service.id);
                          return (
                            <button
                              key={service.id}
                              type="button"
                              onClick={() => toggleService(service.id)}
                              className={`text-left p-4 rounded-lg border-2 transition-all ${
                                isSelected
                                  ? 'border-[#457B9D] bg-blue-50 dark:bg-blue-950/30'
                                  : 'border-gray-200 dark:border-white/10 hover:border-gray-300 dark:hover:border-white/20 bg-white dark:bg-[#151515]'
                              }`}
                            >
                              <div className="flex items-start gap-3">
                                <div
                                  className={`mt-1 w-5 h-5 rounded border-2 flex items-center justify-center flex-shrink-0 ${
                                    isSelected
                                      ? 'border-[#457B9D] bg-[#457B9D]'
                                      : 'border-gray-300 dark:border-gray-600'
                                  }`}
                                >
                                  {isSelected && <CheckCircle2 className="text-white" size={14} />}
                                </div>
                                <div className="flex-1 min-w-0">
                                  <h4 className="font-medium mb-1 text-gray-900 dark:text-white">
                                    {service.name}
                                  </h4>
                                  <p className="text-xs mb-2 text-gray-600 dark:text-gray-400">
                                    {service.description}
                                  </p>
                                  <p className="text-sm font-bold text-[#E63946]">
                                    ₱{service.estimatedPrice.min.toLocaleString()} - ₱
                                    {service.estimatedPrice.max.toLocaleString()}
                                  </p>
                                </div>
                              </div>
                            </button>
                          );
                        })}
                      </div>
                    )}
                  </div>
                );
              })}
            </div>
          </Card>

          {/* Brand Selection (if services with brands are selected) */}
          {selectedServices.length > 0 &&
            services.filter(s => selectedServices.includes(s.id) && s.brands && s.brands.length > 0).length > 0 && (
            <Card className="border-2 border-[#457B9D]">
              <div className="flex items-center gap-2 mb-4">
                <Tag className="text-[#457B9D]" size={24} />
                <h2 className="text-xl font-bold text-gray-900 dark:text-white">
                  Select Preferred Brands (Optional)
                </h2>
              </div>

              <p className="text-sm text-gray-600 dark:text-gray-400 mb-4">
                Choose your preferred brands for the selected services. If no brand is selected,
                our staff will recommend the best option based on your vehicle and budget.
              </p>

              <div className="space-y-6">
                {services
                  .filter(s => selectedServices.includes(s.id) && s.brands && s.brands.length > 0)
                  .map((service) => (
                    <div key={service.id} className="bg-gray-50 dark:bg-[#151515] rounded-lg p-4">
                      <div className="flex items-start gap-3 mb-3">
                        <CheckCircle2 className="text-[#457B9D] flex-shrink-0 mt-0.5" size={20} />
                        <div className="flex-1">
                          <h4 className="font-bold text-gray-900 dark:text-white mb-1">{service.name}</h4>
                          <p className="text-xs text-gray-500 dark:text-gray-400">Available brands:</p>
                        </div>
                      </div>

                      <div className="flex flex-wrap gap-2 ml-8">
                        {service.brands!.map((brand) => (
                          <button
                            key={brand}
                            type="button"
                            onClick={() => handleBrandSelection(service.id, brand)}
                            className={`px-3 py-2 rounded-lg text-sm transition-all ${
                              selectedBrands[service.id] === brand
                                ? 'bg-[#457B9D] text-white border-2 border-[#457B9D]'
                                : 'bg-white dark:bg-[#0B0B0B] text-gray-700 dark:text-gray-300 border-2 border-gray-300 dark:border-white/20 hover:border-[#457B9D]'
                            }`}
                          >
                            {brand}
                          </button>
                        ))}
                      </div>

                      {selectedBrands[service.id] && (
                        <div className="ml-8 mt-3 p-2 bg-blue-50 dark:bg-blue-950/30 border-l-4 border-[#457B9D] rounded">
                          <p className="text-xs text-gray-700 dark:text-gray-300">
                            <strong>Selected:</strong> {selectedBrands[service.id]}
                          </p>
                        </div>
                      )}
                    </div>
                  ))}
              </div>

              <div className="mt-4 p-4 bg-amber-50 dark:bg-amber-950/30 rounded-lg border border-amber-200 dark:border-amber-800/50">
                <p className="text-sm text-gray-700 dark:text-gray-300">
                  <strong>Note:</strong> Brand selection is optional. Our experienced staff will provide
                  recommendations based on quality, compatibility, and your budget during the inspection.
                  Final brand and pricing will be confirmed before work begins.
                </p>
              </div>
            </Card>
          )}

            {/* Estimated Cost Summary */}
            {estimatedCost && (
              <Card className="bg-gradient-to-r from-[#E63946] to-[#D62839] text-white">
                <div className="flex items-start gap-4">
                  <div className="p-3 bg-white/20 rounded-lg">
                    <DollarSign size={32} />
                  </div>
                  <div className="flex-1">
                    <h3 className="text-lg font-bold mb-2">Estimated Total Cost Range</h3>
                    <p className="text-3xl font-bold mb-3">{estimatedCost.display}</p>
                    <div className="bg-white/20 rounded-lg p-3 text-sm space-y-2">
                      <p className="font-medium mb-1">Selected Services ({selectedServices.length}):</p>
                      <ul className="space-y-1 opacity-90">
                        {services
                          .filter((s) => selectedServices.includes(s.id))
                          .map((s) => (
                            <li key={s.id} className="flex items-start gap-2">
                              <span>•</span>
                              <div className="flex-1">
                                <span>{s.name}</span>
                                {selectedBrands[s.id] && (
                                  <span className="ml-2 text-xs bg-white/20 px-2 py-0.5 rounded">
                                    {selectedBrands[s.id]}
                                  </span>
                                )}
                              </div>
                            </li>
                          ))}
                      </ul>
                    </div>
                    <p className="text-sm mt-3 text-white/90">
                      ⚠️ Final pricing will be determined after vehicle inspection by our qualified
                      staff. Prices include quality branded parts and labor.
                    </p>
                  </div>
                </div>
              </Card>
            )}

            {/* Action Button */}
            <div className="flex gap-4 justify-end pb-8">
              <Button
                type="button"
                variant="outline"
                onClick={() => navigate('/customer')}
                className="border-red-500 text-red-500 hover:bg-red-500 hover:text-white dark:border-red-500 dark:text-red-500 dark:hover:bg-red-500 dark:hover:text-white"
              >
                Cancel
              </Button>
              <Button
                type="button"
                size="lg"
                onClick={handleProceedToDetails}
                className="bg-green-600 text-white hover:bg-green-700 border-green-600 hover:border-green-700 dark:bg-green-600 dark:hover:bg-green-700"
              >
                Proceed to Booking Details
                <ArrowRight size={20} className="ml-2" />
              </Button>
            </div>
          </>
        )}

        {/* STEP 2: Booking Details */}
        {currentStep === 2 && (
          <form onSubmit={handleProceedToPayment} className="space-y-6">
            {/* Customer Information */}
            <Card>
            <h2 className="text-xl font-bold text-gray-900 dark:text-white mb-4">Customer Information</h2>
            <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
              <Input
                label="Full Name"
                name="customerName"
                value={formData.customerName}
                onChange={handleChange}
                required
              />
              <Input
                label="Contact Number"
                name="contactNumber"
                type="tel"
                value={formData.contactNumber}
                onChange={handleChange}
                required
              />
            </div>
          </Card>

          {/* Vehicle Information */}
          <Card>
            <h2 className="text-xl font-bold text-gray-900 dark:text-white mb-4">Vehicle Information</h2>
            <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
              <Input
                label="Vehicle Make"
                name="vehicleMake"
                placeholder="e.g., Honda, Toyota"
                value={formData.vehicleMake}
                onChange={handleChange}
                required
              />
              <Input
                label="Vehicle Model"
                name="vehicleModel"
                placeholder="e.g., Civic, Supra"
                value={formData.vehicleModel}
                onChange={handleChange}
                required
              />
              <Input
                label="Vehicle Year"
                name="vehicleYear"
                type="number"
                placeholder="e.g., 2020"
                value={formData.vehicleYear}
                onChange={handleChange}
                required
              />
              <Input
                label="Plate Number"
                name="plateNumber"
                placeholder="e.g., ABC 1234"
                value={formData.plateNumber}
                onChange={handleChange}
                required
              />
            </div>
          </Card>

          {/* Appointment Details */}
          <Card>
            <div className="flex items-center gap-2 mb-4">
              <Calendar className="text-[#457B9D]" size={24} />
              <h2 className="text-xl font-bold text-gray-900 dark:text-white">Appointment Details</h2>
            </div>

            <div className="space-y-6">
              {/* Date Selection */}
              <div>
                <label className="block text-sm font-medium mb-2 text-gray-900 dark:text-white">
                  Select Preferred Date *
                </label>
                <Input
                  name="preferredDate"
                  type="date"
                  value={formData.preferredDate}
                  onChange={(e) => {
                    handleChange(e);
                    setSelectedTimeSlot('');
                    setFormData({ ...formData, preferredDate: e.target.value, preferredTime: '' });
                  }}
                  min={new Date().toISOString().split('T')[0]}
                  required
                />

                {formData.preferredDate && (
                  <div className={`mt-3 p-3 rounded-lg border ${
                    isSunday(formData.preferredDate) || getDateAvailability(formData.preferredDate).isFullyBooked
                      ? 'bg-red-50 dark:bg-red-950/30 border-red-200 dark:border-red-800'
                      : 'bg-blue-50 dark:bg-blue-950/30 border-blue-200 dark:border-blue-800'
                  }`}>
                    <div className="flex items-start gap-2">
                      {isSunday(formData.preferredDate) || getDateAvailability(formData.preferredDate).isFullyBooked ? (
                        <AlertCircle size={16} className="text-red-600 dark:text-red-400 mt-0.5 flex-shrink-0" />
                      ) : (
                        <Info size={16} className="text-blue-600 dark:text-blue-400 mt-0.5 flex-shrink-0" />
                      )}
                      <div className="text-sm">
                        {(() => {
                          if (isSunday(formData.preferredDate)) {
                            return (
                              <p className="text-red-600 dark:text-red-400 font-medium">
                                We are closed on Sundays. Please select a weekday (Monday-Saturday).
                              </p>
                            );
                          }
                          const availability = getDateAvailability(formData.preferredDate);
                          if (availability.isFullyBooked) {
                            return (
                              <p className="text-red-600 dark:text-red-400 font-medium">
                                This date is fully booked. Please select another date.
                              </p>
                            );
                          }
                          return (
                            <div className="text-blue-800 dark:text-blue-200">
                              <p className="font-medium mb-1">
                                {availability.availableSlots.length} time slots available
                              </p>
                              {availability.bookedSlots.length > 0 && (
                                <p className="text-xs text-blue-700 dark:text-blue-300">
                                  {availability.bookedSlots.length} slots already booked
                                </p>
                              )}
                            </div>
                          );
                        })()}
                      </div>
                    </div>
                  </div>
                )}
              </div>

              {/* Time Slot Selection */}
              {formData.preferredDate && !isSunday(formData.preferredDate) && !getDateAvailability(formData.preferredDate).isFullyBooked && (
                <div>
                  <label className="block text-sm font-medium mb-3 text-gray-900 dark:text-white">
                    Select Preferred Time Slot *
                  </label>

                  <div className="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-5 gap-2">
                    {timeSlots.map((slot) => {
                      const bookedSlots = bookedAppointments[formData.preferredDate] || [];
                      const isBooked = bookedSlots.includes(slot);
                      const isSelected = selectedTimeSlot === slot;

                      return (
                        <button
                          key={slot}
                          type="button"
                          onClick={() => {
                            if (!isBooked) {
                              setSelectedTimeSlot(slot);
                              setFormData({ ...formData, preferredTime: slot });
                            }
                          }}
                          disabled={isBooked}
                          className={`p-3 rounded-lg border-2 text-sm font-medium transition-all ${
                            isBooked
                              ? 'bg-gray-100 dark:bg-gray-800 border-gray-300 dark:border-gray-600 text-gray-400 dark:text-gray-500 cursor-not-allowed'
                              : isSelected
                              ? 'bg-green-600 border-green-600 text-white'
                              : 'bg-white dark:bg-gray-900 border-gray-300 dark:border-gray-600 text-gray-900 dark:text-white hover:border-[#457B9D] hover:bg-blue-50 dark:hover:bg-blue-950/30'
                          }`}
                        >
                          <div className="flex items-center justify-center gap-1">
                            <Clock size={14} />
                            <span>{slot}</span>
                          </div>
                          {isBooked && (
                            <div className="mt-1 flex items-center justify-center">
                              <X size={12} className="text-red-500" />
                              <span className="text-xs">Booked</span>
                            </div>
                          )}
                          {isSelected && (
                            <div className="mt-1 text-xs">Selected</div>
                          )}
                        </button>
                      );
                    })}
                  </div>

                  {!selectedTimeSlot && (
                    <p className="mt-2 text-sm text-amber-600 dark:text-amber-400">
                      Please select an available time slot
                    </p>
                  )}
                </div>
              )}

              {/* Legend */}
              {formData.preferredDate && (
                <div className="flex flex-wrap gap-4 p-3 bg-gray-50 dark:bg-gray-800 rounded-lg">
                  <div className="flex items-center gap-2">
                    <div className="w-4 h-4 rounded bg-white dark:bg-gray-900 border-2 border-gray-300 dark:border-gray-600"></div>
                    <span className="text-xs text-gray-700 dark:text-gray-300">Available</span>
                  </div>
                  <div className="flex items-center gap-2">
                    <div className="w-4 h-4 rounded bg-green-600"></div>
                    <span className="text-xs text-gray-700 dark:text-gray-300">Selected</span>
                  </div>
                  <div className="flex items-center gap-2">
                    <div className="w-4 h-4 rounded bg-gray-100 dark:bg-gray-800 border-2 border-gray-300 dark:border-gray-600"></div>
                    <span className="text-xs text-gray-700 dark:text-gray-300">Booked</span>
                  </div>
                </div>
              )}

              <TextArea
                label="Additional Notes (Optional)"
                name="notes"
                rows={4}
                placeholder="Any specific requirements, preferred brands, or special instructions..."
                value={formData.notes}
                onChange={handleChange}
              />
            </div>
          </Card>

            {/* Actions */}
            <div className="flex gap-4 justify-between pb-8">
              <Button type="button" variant="outline" onClick={handleBackToServices}>
                <ArrowLeft size={20} className="mr-2" />
                Back to Services
              </Button>
              <div className="flex gap-4">
                <Button
                  type="button"
                  variant="outline"
                  onClick={() => navigate('/customer')}
                  className="border-red-500 text-red-500 hover:bg-red-500 hover:text-white dark:border-red-500 dark:text-red-500 dark:hover:bg-red-500 dark:hover:text-white"
                >
                  Cancel
                </Button>
                <Button
                  type="submit"
                  size="lg"
                  className="bg-green-600 text-white hover:bg-green-700 border-green-600 hover:border-green-700 dark:bg-green-600 dark:hover:bg-green-700"
                >
                  Continue to Payment
                  <ArrowRight size={20} className="ml-2" />
                </Button>
              </div>
            </div>
          </form>
        )}

        {/* STEP 3: Payment & Terms */}
        {currentStep === 3 && (
          <form onSubmit={handleSubmit} className="space-y-6">
            {/* Important Notice */}
            <Card className="bg-blue-50 dark:bg-blue-950/30 border-l-4 border-[#457B9D]">
              <div className="flex gap-3">
                <Info className="text-[#457B9D] flex-shrink-0" size={24} />
                <div className="text-sm">
                  <p className="font-medium mb-1 text-gray-900 dark:text-white">Reservation Fee Required: ₱200</p>
                  <p className="text-gray-700 dark:text-gray-300">
                    A non-refundable reservation fee of ₱200 is required to secure your appointment.
                    This fee ensures your commitment to arrive at the scheduled date and time.
                  </p>
                </div>
              </div>
            </Card>

          {/* Reservation Fee Payment */}
          <Card className="border-2 border-[#457B9D]">
            <div className="flex items-center gap-2 mb-4">
              <Smartphone className="text-[#457B9D]" size={24} />
              <h2 className="text-xl font-bold text-gray-900 dark:text-white">Reservation Fee Payment - ₱200</h2>
            </div>

            <div className="space-y-6">
              {/* Payment Method Selection */}
              <div className="space-y-4">
                <label className="block">
                  <span className="text-sm font-medium text-gray-700 dark:text-gray-300 mb-2 block">
                    Select Payment Method <span className="text-red-500">*</span>
                  </span>
                  <div className="grid grid-cols-2 gap-4">
                    <button
                      type="button"
                      onClick={() => setPaymentMethod('gcash')}
                      className={`p-4 border-2 rounded-lg transition-all ${
                        paymentMethod === 'gcash'
                          ? 'border-[#457B9D] bg-blue-50 dark:bg-blue-950/30 text-gray-900 dark:text-white'
                          : 'border-gray-300 dark:border-white/20 hover:border-gray-400 dark:hover:border-white/30 text-gray-900 dark:text-white'
                      }`}
                    >
                      <div className="flex items-center justify-center gap-2">
                        <Smartphone size={20} />
                        <span className="font-medium">GCash</span>
                      </div>
                    </button>
                    <button
                      type="button"
                      onClick={() => setPaymentMethod('maya')}
                      className={`p-4 border-2 rounded-lg transition-all ${
                        paymentMethod === 'maya'
                          ? 'border-[#457B9D] bg-blue-50 dark:bg-blue-950/30 text-gray-900 dark:text-white'
                          : 'border-gray-300 dark:border-white/20 hover:border-gray-400 dark:hover:border-white/30 text-gray-900 dark:text-white'
                      }`}
                    >
                      <div className="flex items-center justify-center gap-2">
                        <Smartphone size={20} />
                        <span className="font-medium">Maya</span>
                      </div>
                    </button>
                  </div>
                </label>

                {/* QR Code Display */}
                {paymentMethod === 'gcash' && (
                  <div className="bg-white dark:bg-[#0B0B0B] rounded-lg p-6 border-2 border-[#457B9D]">
                    <div className="text-center space-y-4">
                      <div className="inline-block bg-blue-50 dark:bg-blue-950/30 px-4 py-2 rounded-lg">
                        <p className="text-sm font-medium text-gray-900 dark:text-white">
                          Scan QR Code to Pay ₱200
                        </p>
                      </div>
                      <div className="flex justify-center">
                        <div className="bg-white p-4 rounded-xl shadow-lg">
                          <img
                            src={gcashQRCode}
                            alt="GCash QR Code - AutoProject-D"
                            className="w-64 h-64 object-contain"
                          />
                        </div>
                      </div>
                      <div className="bg-gray-50 dark:bg-[#151515] rounded-lg p-4">
                        <p className="text-sm text-gray-700 dark:text-gray-300 font-medium mb-2">
                          Payment Instructions:
                        </p>
                        <ol className="text-xs text-gray-600 dark:text-gray-400 space-y-1 text-left">
                          <li>1. Open your GCash app</li>
                          <li>2. Scan the QR code above</li>
                          <li>3. Confirm payment of ₱200</li>
                          <li>4. Take a screenshot of your receipt</li>
                          <li>5. Enter your reference number below</li>
                        </ol>
                      </div>
                      <p className="text-xs text-gray-600 dark:text-gray-400">
                        <strong>Account Name:</strong> AutoProject-D Custom Garage
                      </p>
                    </div>
                  </div>
                )}

                {paymentMethod === 'maya' && (
                  <div className="bg-white dark:bg-[#0B0B0B] rounded-lg p-6 border-2 border-[#457B9D]">
                    <div className="text-center space-y-4">
                      <div className="inline-block bg-blue-50 dark:bg-blue-950/30 px-4 py-2 rounded-lg">
                        <p className="text-sm font-medium text-gray-900 dark:text-white">
                          Send ₱200 to Maya Account
                        </p>
                      </div>
                      <div className="bg-gray-50 dark:bg-[#151515] rounded-lg p-4">
                        <p className="text-lg font-bold text-gray-900 dark:text-white mb-2">
                          0917-888-9999
                        </p>
                        <p className="text-sm text-gray-600 dark:text-gray-400">
                          Account Name: AutoProject-D Custom Garage
                        </p>
                      </div>
                      <div className="bg-gray-50 dark:bg-[#151515] rounded-lg p-4">
                        <p className="text-sm text-gray-700 dark:text-gray-300 font-medium mb-2">
                          Payment Instructions:
                        </p>
                        <ol className="text-xs text-gray-600 dark:text-gray-400 space-y-1 text-left">
                          <li>1. Open your Maya app</li>
                          <li>2. Select "Send Money"</li>
                          <li>3. Enter the mobile number above</li>
                          <li>4. Send ₱200</li>
                          <li>5. Take a screenshot of your receipt</li>
                          <li>6. Enter your reference number below</li>
                        </ol>
                      </div>
                    </div>
                  </div>
                )}

                <Input
                  label="Payment Reference Number"
                  name="referenceNumber"
                  placeholder="e.g., 1234567890123"
                  value={referenceNumber}
                  onChange={(e) => setReferenceNumber(e.target.value)}
                  required
                  helperText="Enter the reference number from your payment receipt"
                />
              </div>
            </div>
          </Card>

          {/* Terms and Conditions */}
          <Card className="bg-amber-50 dark:bg-amber-950/30 border-l-4 border-amber-500">
            <h2 className="text-xl font-bold text-gray-900 dark:text-white mb-4 flex items-center gap-2">
              <AlertCircle className="text-amber-500" size={24} />
              Terms and Conditions
            </h2>

            <div className="space-y-4">
              {/* Terms List */}
              <div className="bg-white dark:bg-[#151515] rounded-lg p-4 space-y-3">
                <p className="font-medium text-gray-900 dark:text-white">Please read and agree to the following:</p>
                <ul className="space-y-2 text-sm text-gray-700 dark:text-gray-300">
                  <li className="flex gap-2">
                    <CheckCircle2 size={16} className="text-green-600 flex-shrink-0 mt-0.5" />
                    <span>
                      The <strong>₱200 reservation fee</strong> is required to secure your
                      appointment and confirm your commitment.
                    </span>
                  </li>
                  <li className="flex gap-2">
                    <CheckCircle2 size={16} className="text-green-600 flex-shrink-0 mt-0.5" />
                    <span>
                      You must arrive at the <strong>exact scheduled date and time</strong>.
                    </span>
                  </li>
                  <li className="flex gap-2">
                    <CheckCircle2 size={16} className="text-green-600 flex-shrink-0 mt-0.5" />
                    <span>
                      A <strong>30-minute grace period</strong> is allowed if you are delayed.
                    </span>
                  </li>
                  <li className="flex gap-2">
                    <AlertCircle size={16} className="text-amber-500 flex-shrink-0 mt-0.5" />
                    <span>
                      If you <strong>do not arrive within 30 minutes</strong> of your scheduled
                      time, the reservation fee is <strong>non-refundable</strong>.
                    </span>
                  </li>
                  <li className="flex gap-2">
                    <CheckCircle2 size={16} className="text-green-600 flex-shrink-0 mt-0.5" />
                    <span>
                      Final service pricing will be determined after vehicle inspection by our
                      staff.
                    </span>
                  </li>
                  <li className="flex gap-2">
                    <CheckCircle2 size={16} className="text-green-600 flex-shrink-0 mt-0.5" />
                    <span>
                      Your booking is subject to admin approval and payment verification.
                    </span>
                  </li>
                </ul>
              </div>

              {/* Checkbox Agreement */}
              <div className="flex items-start gap-3 p-4 bg-white dark:bg-[#151515] rounded-lg border-2 border-gray-300 dark:border-white/20">
                <input
                  type="checkbox"
                  checked={agreedToTerms}
                  onChange={(e) => setAgreedToTerms(e.target.checked)}
                  className="mt-1 w-4 h-4 cursor-pointer"
                  required
                />
                <label className="text-sm cursor-pointer select-none">
                  <span className="font-medium text-gray-900 dark:text-white">
                    I have read and agree to the terms and conditions.
                  </span>
                  <span className="block text-gray-600 dark:text-gray-400 mt-1">
                    I understand that the ₱200 reservation fee is non-refundable if I fail to arrive
                    within 30 minutes of my scheduled appointment.
                  </span>
                </label>
              </div>

              {/* Expandable full terms */}
              <button
                type="button"
                onClick={() => setShowTerms(!showTerms)}
                className="text-[#457B9D] text-sm font-medium hover:underline"
              >
                {showTerms ? 'Hide' : 'View'} Full Terms and Conditions
              </button>

              {showTerms && (
                <div className="bg-white dark:bg-[#151515] rounded-lg p-4 border border-gray-300 dark:border-white/20 text-sm text-gray-700 dark:text-gray-300 space-y-3">
                  <h3 className="font-bold text-gray-900 dark:text-white">Full Terms and Conditions</h3>
                  <div className="space-y-2">
                    <p>
                      <strong>1. Reservation Fee Policy</strong>
                    </p>
                    <p>
                      The reservation fee of ₱200 is mandatory for all service bookings. This fee
                      secures your appointment slot and demonstrates your commitment to the
                      scheduled service.
                    </p>

                    <p>
                      <strong>2. Payment Methods</strong>
                    </p>
                    <p>
                      Reservation fees must be paid via GCash or Maya e-wallet services only.
                      Payment must be completed before booking submission.
                    </p>

                    <p>
                      <strong>3. Arrival Policy</strong>
                    </p>
                    <p>
                      Customers are expected to arrive at their exact scheduled date and time. A
                      30-minute grace period is provided for delays. Failure to arrive within this
                      timeframe will result in forfeiture of the reservation fee.
                    </p>

                    <p>
                      <strong>4. Refund Policy</strong>
                    </p>
                    <p>The ₱200 reservation fee is strictly non-refundable under the following conditions:</p>
                    <ul className="list-disc pl-5 space-y-1">
                      <li>Customer does not arrive within 30 minutes of scheduled time</li>
                      <li>Customer cancels the booking less than 24 hours before the appointment</li>
                      <li>Customer fails to show up without prior notice</li>
                    </ul>

                    <p>
                      <strong>5. Service Pricing</strong>
                    </p>
                    <p>
                      The estimated cost ranges provided are for reference only. Final service
                      pricing will be determined after a thorough inspection of your vehicle by our
                      qualified staff. AutoProject-D Custom Garage uses branded, quality parts and
                      the exact brand and price of all parts will be included in your detailed
                      quote.
                    </p>

                    <p>
                      <strong>6. Booking Approval</strong>
                    </p>
                    <p>
                      All bookings are subject to admin approval. The admin will verify your payment
                      and confirm your booking within 24-48 hours.
                    </p>

                    <p>
                      <strong>7. Modifications and Cancellations</strong>
                    </p>
                    <p>
                      Any changes to your booking must be requested at least 24 hours in advance.
                      Contact our support team for assistance.
                    </p>
                  </div>
                </div>
              )}
            </div>
          </Card>

            {/* Actions */}
            <div className="flex gap-4 justify-between pb-8">
              <Button type="button" variant="outline" onClick={handleBackToDetails}>
                <ArrowLeft size={20} className="mr-2" />
                Back to Details
              </Button>
              <div className="flex gap-4">
                <Button
                  type="button"
                  variant="outline"
                  onClick={() => navigate('/customer')}
                  className="border-red-500 text-red-500 hover:bg-red-500 hover:text-white dark:border-red-500 dark:text-red-500 dark:hover:bg-red-500 dark:hover:text-white"
                >
                  Cancel
                </Button>
                <Button
                  type="submit"
                  size="lg"
                  className="bg-green-600 text-white hover:bg-green-700 border-green-600 hover:border-green-700 dark:bg-green-600 dark:hover:bg-green-700"
                >
                  <CheckCircle2 size={20} className="mr-2" />
                  Submit Booking & Payment
                </Button>
              </div>
            </div>
          </form>
        )}
      </div>
    </DashboardLayout>
  );
}