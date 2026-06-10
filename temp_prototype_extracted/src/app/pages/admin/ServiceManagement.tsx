import { useState } from 'react';
import { DashboardLayout } from '../../components/DashboardLayout';
import { Card } from '../../components/Card';
import { Button } from '../../components/Button';
import { Input } from '../../components/FormInputs';
import { Edit, Trash2, Plus, Search, Power } from 'lucide-react';
import { showToast } from '../../utils/toast';

export default function ServiceManagement() {
  const [showAddForm, setShowAddForm] = useState(false);

  const services = [
    {
      id: 1,
      name: 'Engine Customization',
      description: 'Performance engine modifications including turbo, ECU tuning, and internal upgrades',
      minCost: 50000,
      maxCost: 150000,
      duration: '5-7 days',
      status: 'Active',
    },
    {
      id: 2,
      name: 'Body Kit Installation',
      description: 'Complete body kit installation with professional fitment and finishing',
      minCost: 30000,
      maxCost: 80000,
      duration: '3-5 days',
      status: 'Active',
    },
    {
      id: 3,
      name: 'Paint Job',
      description: 'Professional automotive painting with premium materials and finish',
      minCost: 25000,
      maxCost: 100000,
      duration: '4-6 days',
      status: 'Active',
    },
    {
      id: 4,
      name: 'Turbo Installation',
      description: 'Turbocharger installation with supporting modifications and tuning',
      minCost: 60000,
      maxCost: 180000,
      duration: '6-8 days',
      status: 'Active',
    },
    {
      id: 5,
      name: 'Exhaust Fabrication',
      description: 'Custom exhaust system design and fabrication with quality materials',
      minCost: 15000,
      maxCost: 50000,
      duration: '2-3 days',
      status: 'Active',
    },
  ];

  const handleEdit = (serviceId: number) => {
    showToast.info(`Edit service ${serviceId}`);
  };

  const handleDelete = (serviceId: number) => {
    if (confirm('Are you sure you want to delete this service?')) {
      showToast.success(`Service ${serviceId} deleted`);
    }
  };

  const handleToggleStatus = (serviceId: number) => {
    showToast.success(`Service ${serviceId} status toggled`);
  };

  return (
    <DashboardLayout role="admin">
      <div className="space-y-6">
        {/* Header */}
        <div className="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
          <div>
            <h1 className="text-3xl font-bold text-[#1F2937] mb-2">Service Management</h1>
            <p className="text-gray-600">Manage available services and cost estimates.</p>
          </div>
          <Button variant="accent" onClick={() => setShowAddForm(!showAddForm)}>
            <Plus size={20} className="mr-2" />
            Add New Service
          </Button>
        </div>

        {/* Add Service Form */}
        {showAddForm && (
          <Card>
            <h2 className="text-xl font-bold text-[#1F2937] mb-4">Add New Service</h2>
            <form className="space-y-4">
              <Input
                label="Service Name"
                placeholder="e.g., Suspension Upgrade"
                required
              />
              <Input
                label="Description"
                placeholder="Detailed description of the service..."
                required
              />
              <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                <Input
                  label="Minimum Cost (₱)"
                  type="number"
                  placeholder="20000"
                  required
                />
                <Input
                  label="Maximum Cost (₱)"
                  type="number"
                  placeholder="60000"
                  required
                />
              </div>
              <Input
                label="Estimated Duration"
                placeholder="e.g., 3-4 days"
                required
              />
              <div className="flex gap-3">
                <Button type="submit" variant="accent">Add Service</Button>
                <Button type="button" variant="outline" onClick={() => setShowAddForm(false)}>
                  Cancel
                </Button>
              </div>
            </form>
          </Card>
        )}

        {/* Stats */}
        <div className="grid grid-cols-1 md:grid-cols-4 gap-6">
          <Card className="text-center">
            <p className="text-sm text-gray-600 mb-1">Total Services</p>
            <p className="text-3xl font-bold text-[#1F2937]">{services.length}</p>
          </Card>
          <Card className="text-center">
            <p className="text-sm text-gray-600 mb-1">Active Services</p>
            <p className="text-3xl font-bold text-green-600">
              {services.filter(s => s.status === 'Active').length}
            </p>
          </Card>
          <Card className="text-center">
            <p className="text-sm text-gray-600 mb-1">Most Popular</p>
            <p className="text-lg font-bold text-[#E63946]">Engine Custom.</p>
          </Card>
          <Card className="text-center">
            <p className="text-sm text-gray-600 mb-1">Avg. Cost Range</p>
            <p className="text-lg font-bold text-[#457B9D]">₱25K-₱90K</p>
          </Card>
        </div>

        {/* Services List */}
        <div className="space-y-4">
          {services.map((service) => (
            <Card key={service.id}>
              <div className="flex flex-col lg:flex-row gap-6">
                <div className="flex-1 space-y-3">
                  <div className="flex items-start justify-between gap-4">
                    <div>
                      <h3 className="text-xl font-bold text-[#1F2937] mb-2">{service.name}</h3>
                      <p className="text-gray-600 mb-3">{service.description}</p>
                    </div>
                    <span className={`px-3 py-1 rounded-full text-xs font-medium flex-shrink-0 ${
                      service.status === 'Active' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800'
                    }`}>
                      {service.status}
                    </span>
                  </div>

                  <div className="grid grid-cols-1 sm:grid-cols-3 gap-4 text-sm">
                    <div>
                      <p className="text-gray-600 mb-1">Cost Range</p>
                      <p className="font-bold text-[#E63946]">
                        ₱{service.minCost.toLocaleString()} - ₱{service.maxCost.toLocaleString()}
                      </p>
                    </div>
                    <div>
                      <p className="text-gray-600 mb-1">Duration</p>
                      <p className="font-medium text-[#1F2937]">{service.duration}</p>
                    </div>
                    <div>
                      <p className="text-gray-600 mb-1">Service ID</p>
                      <p className="font-medium text-[#1F2937]">SRV-{service.id}</p>
                    </div>
                  </div>
                </div>

                <div className="flex lg:flex-col gap-2 justify-end lg:justify-start">
                  <Button
                    variant="secondary"
                    size="sm"
                    onClick={() => handleEdit(service.id)}
                  >
                    <Edit size={16} className="mr-2" />
                    Edit
                  </Button>
                  <Button
                    variant="outline"
                    size="sm"
                    onClick={() => handleToggleStatus(service.id)}
                  >
                    {service.status === 'Active' ? 'Deactivate' : 'Activate'}
                  </Button>
                  <Button
                    variant="outline"
                    size="sm"
                    onClick={() => handleDelete(service.id)}
                    className="text-red-600 hover:bg-red-50 hover:border-red-600"
                  >
                    <Trash2 size={16} className="mr-2" />
                    Delete
                  </Button>
                </div>
              </div>
            </Card>
          ))}
        </div>

        {/* Cost Estimation Tips */}
        <Card className="bg-[#457B9D]/10">
          <h3 className="font-bold text-[#1F2937] mb-3">💡 Cost Estimation Guidelines</h3>
          <ul className="space-y-2 text-sm text-gray-700">
            <li>• Set realistic cost ranges based on parts, labor, and materials</li>
            <li>• Consider variations in vehicle models and customization complexity</li>
            <li>• Include buffer for unexpected issues or additional work</li>
            <li>• Review and update costs regularly based on market prices</li>
            <li>• Provide detailed breakdown during booking approval process</li>
          </ul>
        </Card>
      </div>
    </DashboardLayout>
  );
}