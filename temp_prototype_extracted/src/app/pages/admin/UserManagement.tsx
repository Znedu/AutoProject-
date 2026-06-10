import { useState } from 'react';
import { DashboardLayout } from '../../components/DashboardLayout';
import { Card } from '../../components/Card';
import { Button } from '../../components/Button';
import { Input } from '../../components/FormInputs';
import { Edit, Trash2, Plus, Search } from 'lucide-react';
import { showToast } from '../../utils/toast';

export default function UserManagement() {
  const [searchQuery, setSearchQuery] = useState('');
  
  const users = [
    {
      id: 1,
      name: 'Juan Dela Cruz',
      email: 'juan.delacruz@email.com',
      phone: '+63 912 345 6789',
      role: 'Customer',
      status: 'Active',
      joinDate: 'Jan 15, 2024',
    },
    {
      id: 2,
      name: 'Maria Santos',
      email: 'maria.santos@email.com',
      phone: '+63 917 888 9999',
      role: 'Customer',
      status: 'Active',
      joinDate: 'Feb 20, 2024',
    },
    {
      id: 3,
      name: 'John Mechanic',
      email: 'john.m@autoproject.com',
      phone: '+63 920 111 2222',
      role: 'Mechanic',
      status: 'Active',
      joinDate: 'Jan 5, 2023',
    },
    {
      id: 4,
      name: 'Sarah Staff',
      email: 'sarah.s@autoproject.com',
      phone: '+63 923 333 4444',
      role: 'Staff',
      status: 'Active',
      joinDate: 'Mar 10, 2023',
    },
    {
      id: 5,
      name: 'Pedro Rodriguez',
      email: 'pedro.r@email.com',
      phone: '+63 915 555 6666',
      role: 'Customer',
      status: 'Inactive',
      joinDate: 'Dec 1, 2023',
    },
  ];

  const handleEdit = (userId: number) => {
    showToast.info(`Edit user ${userId}`);
  };

  const handleDelete = (userId: number) => {
    if (confirm('Are you sure you want to delete this user?')) {
      showToast.success(`User ${userId} deleted`);
    }
  };

  return (
    <DashboardLayout role="admin">
      <div className="space-y-6">
        {/* Header */}
        <div className="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
          <div>
            <h1 className="text-3xl font-bold text-[#1F2937] mb-2">User Management</h1>
            <p className="text-gray-600">Manage system users and roles.</p>
          </div>
          <Button variant="accent">
            <Plus size={20} className="mr-2" />
            Add New User
          </Button>
        </div>

        {/* Filters & Search */}
        <Card>
          <div className="flex flex-col sm:flex-row gap-4">
            <div className="flex-1 relative">
              <Search className="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400" size={20} />
              <Input
                placeholder="Search users by name, email, or phone..."
                value={searchQuery}
                onChange={(e) => setSearchQuery(e.target.value)}
                className="pl-10"
              />
            </div>
            <div className="flex flex-wrap gap-2">
              <Button variant="primary" size="sm">All Users</Button>
              <Button variant="ghost" size="sm">Customers</Button>
              <Button variant="ghost" size="sm">Staff</Button>
              <Button variant="ghost" size="sm">Mechanics</Button>
              <Button variant="ghost" size="sm">Admins</Button>
            </div>
          </div>
        </Card>

        {/* Stats */}
        <div className="grid grid-cols-1 md:grid-cols-4 gap-6">
          <Card className="text-center">
            <p className="text-sm text-gray-600 mb-1">Total Users</p>
            <p className="text-3xl font-bold text-[#1F2937]">{users.length}</p>
          </Card>
          <Card className="text-center">
            <p className="text-sm text-gray-600 mb-1">Customers</p>
            <p className="text-3xl font-bold text-[#457B9D]">
              {users.filter(u => u.role === 'Customer').length}
            </p>
          </Card>
          <Card className="text-center">
            <p className="text-sm text-gray-600 mb-1">Staff</p>
            <p className="text-3xl font-bold text-[#E63946]">
              {users.filter(u => u.role === 'Staff').length}
            </p>
          </Card>
          <Card className="text-center">
            <p className="text-sm text-gray-600 mb-1">Mechanics</p>
            <p className="text-3xl font-bold text-green-600">
              {users.filter(u => u.role === 'Mechanic').length}
            </p>
          </Card>
        </div>

        {/* Users Table */}
        <Card>
          <div className="overflow-x-auto">
            <table className="w-full">
              <thead>
                <tr className="border-b border-gray-200">
                  <th className="text-left py-3 px-4 font-medium text-gray-600">Name</th>
                  <th className="text-left py-3 px-4 font-medium text-gray-600">Contact</th>
                  <th className="text-left py-3 px-4 font-medium text-gray-600">Role</th>
                  <th className="text-left py-3 px-4 font-medium text-gray-600">Status</th>
                  <th className="text-left py-3 px-4 font-medium text-gray-600">Join Date</th>
                  <th className="text-left py-3 px-4 font-medium text-gray-600">Actions</th>
                </tr>
              </thead>
              <tbody>
                {users.map((user) => (
                  <tr key={user.id} className="border-b border-gray-100 hover:bg-gray-50">
                    <td className="py-3 px-4">
                      <p className="font-medium text-[#1F2937]">{user.name}</p>
                    </td>
                    <td className="py-3 px-4">
                      <p className="text-sm text-gray-600">{user.email}</p>
                      <p className="text-xs text-gray-500">{user.phone}</p>
                    </td>
                    <td className="py-3 px-4">
                      <span className={`px-3 py-1 rounded-full text-xs font-medium ${
                        user.role === 'Customer' ? 'bg-blue-100 text-blue-800' :
                        user.role === 'Mechanic' ? 'bg-green-100 text-green-800' :
                        user.role === 'Staff' ? 'bg-purple-100 text-purple-800' :
                        'bg-red-100 text-red-800'
                      }`}>
                        {user.role}
                      </span>
                    </td>
                    <td className="py-3 px-4">
                      <span className={`px-3 py-1 rounded-full text-xs font-medium ${
                        user.status === 'Active' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800'
                      }`}>
                        {user.status}
                      </span>
                    </td>
                    <td className="py-3 px-4 text-sm text-gray-600">
                      {user.joinDate}
                    </td>
                    <td className="py-3 px-4">
                      <div className="flex gap-2">
                        <button
                          onClick={() => handleEdit(user.id)}
                          className="p-2 hover:bg-gray-100 rounded-lg transition-colors"
                        >
                          <Edit size={16} className="text-[#457B9D]" />
                        </button>
                        <button
                          onClick={() => handleDelete(user.id)}
                          className="p-2 hover:bg-red-50 rounded-lg transition-colors"
                        >
                          <Trash2 size={16} className="text-red-600" />
                        </button>
                      </div>
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