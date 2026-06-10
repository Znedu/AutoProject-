import { useState } from 'react';
import { DashboardLayout } from '../../components/DashboardLayout';
import { Card } from '../../components/Card';
import { Button } from '../../components/Button';
import { Input } from '../../components/FormInputs';
import { User, Mail, Phone, MapPin, Edit } from 'lucide-react';
import { showToast } from '../../utils/toast';

export default function Profile() {
  const [isEditing, setIsEditing] = useState(false);
  const [profileData, setProfileData] = useState({
    fullName: 'Juan Dela Cruz',
    email: 'juan.delacruz@email.com',
    phone: '+63 912 345 6789',
    address: '123 Main Street, Quezon City, Metro Manila',
  });

  const handleSubmit = (e: React.FormEvent) => {
    e.preventDefault();
    setIsEditing(false);
    showToast.success('Profile updated successfully!');
  };

  return (
    <DashboardLayout role="customer">
      <div className="max-w-4xl mx-auto space-y-6">
        {/* Header */}
        <div className="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
          <div>
            <h1 className="text-3xl font-bold mb-2 text-[#000000]">My Profile</h1>
            <p className="text-[#000000]">Manage your account information.</p>
          </div>
          {!isEditing && (
            <Button variant="secondary" onClick={() => setIsEditing(true)}>
              <Edit size={20} className="mr-2" />
              Edit Profile
            </Button>
          )}
        </div>

        {/* Profile Card */}
        <Card>
          <div className="flex flex-col items-center mb-8">
            <div className="w-24 h-24 rounded-full bg-gradient-to-br from-[#E63946] to-[#D62839] flex items-center justify-center text-white text-3xl font-bold mb-4">
              JD
            </div>
            <h2 className="text-2xl font-bold text-[#000000]">{profileData.fullName}</h2>
            <p className="text-[#000000]">Customer</p>
          </div>

          <form onSubmit={handleSubmit} className="space-y-6">
            <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
              <div className="flex items-start gap-4">
                <div className="p-3 rounded-lg bg-[#f3f4f6]">
                  <User size={24} className="text-[#1F2937]" />
                </div>
                <div className="flex-1">
                  {isEditing ? (
                    <Input
                      label="Full Name"
                      value={profileData.fullName}
                      onChange={(e) =>
                        setProfileData({ ...profileData, fullName: e.target.value })
                      }
                    />
                  ) : (
                    <>
                      <p className="text-sm mb-1 text-[#000000]">Full Name</p>
                      <p className="font-medium text-[#111010]">{profileData.fullName}</p>
                    </>
                  )}
                </div>
              </div>

              <div className="flex items-start gap-4">
                <div className="p-3 bg-gray-100 rounded-lg">
                  <Mail size={24} className="text-[#1F2937]" />
                </div>
                <div className="flex-1">
                  {isEditing ? (
                    <Input
                      label="Email Address"
                      type="email"
                      value={profileData.email}
                      onChange={(e) =>
                        setProfileData({ ...profileData, email: e.target.value })
                      }
                    />
                  ) : (
                    <>
                      <p className="text-sm mb-1 text-[#000000]">Email Address</p>
                      <p className="font-medium text-[#121111]">{profileData.email}</p>
                    </>
                  )}
                </div>
              </div>

              <div className="flex items-start gap-4">
                <div className="p-3 bg-gray-100 rounded-lg">
                  <Phone size={24} className="text-[#1F2937]" />
                </div>
                <div className="flex-1">
                  {isEditing ? (
                    <Input
                      label="Phone Number"
                      type="tel"
                      value={profileData.phone}
                      onChange={(e) =>
                        setProfileData({ ...profileData, phone: e.target.value })
                      }
                    />
                  ) : (
                    <>
                      <p className="text-sm mb-1 text-[#000000]">Phone Number</p>
                      <p className="font-medium text-[#161515]">{profileData.phone}</p>
                    </>
                  )}
                </div>
              </div>

              <div className="flex items-start gap-4">
                <div className="p-3 bg-gray-100 rounded-lg">
                  <MapPin size={24} className="text-[#1F2937]" />
                </div>
                <div className="flex-1">
                  {isEditing ? (
                    <Input
                      label="Address"
                      value={profileData.address}
                      onChange={(e) =>
                        setProfileData({ ...profileData, address: e.target.value })
                      }
                    />
                  ) : (
                    <>
                      <p className="text-sm mb-1 text-[#000000]">Address</p>
                      <p className="font-medium text-[#000000]">{profileData.address}</p>
                    </>
                  )}
                </div>
              </div>
            </div>

            {isEditing && (
              <div className="flex gap-3 justify-end pt-4 border-t">
                <Button
                  type="button"
                  variant="outline"
                  onClick={() => setIsEditing(false)}
                >
                  Cancel
                </Button>
                <Button className="text-[#000000] text-[#000000] text-[#000000] text-[#000000] text-[#000000] text-[#000000] text-[#000000] text-[#000000] text-[#000000] text-[#000000] text-[#000000] text-[#1a1a1a] text-[#1b1b1b] text-[#1b1a1a] text-[#1d1c1c] text-[#262424] text-[#292626] text-[#2e2929] text-[#2f2929] text-[#322b2b] text-[#332b2b] text-[#342b2b] text-[#342b2b] text-[#342b2b] text-[#342a2a] text-[#352a2a] text-[#352a2a] text-[#352a2a] text-[#362a2a] text-[#3b2929] text-[#3e2828] text-[#432525] text-[#4a2222] text-[#552020] text-[#622020] text-[#7c2020] text-[#981d1d] text-[#b71616] text-[#d70b0b] text-[#e30303] text-[#f20000] text-[#fe0000] text-[#ff0000] text-[#ff0000] text-[#ff0000] text-[#ff0000] text-[#ff0000] text-[#ff0000] text-[#ff0000] text-[#ff0000] text-[#ff0000] text-[#ff0000] text-[#ff0000] text-[#ff0000] text-[#ff0000] text-[#ff0000] text-[#ff0000] text-[#ff0000] text-[#ff0000] text-[#ff0000] text-[#ff0000] text-[#ff0000] text-[#ff0000] text-[#ff0000] text-[#00ff33] text-[#00ff33] text-[#09ff3a] text-[#08ff39] text-[#08ff39] text-[#06fa36] text-[#06f836] text-[#05f334] text-[#04f033] text-[#04eb32] text-[#03e931] text-[#02e62f] text-[#02e52f] text-[#01e12e] text-[#01df2d] text-[#00d52a] text-[#00ce29] text-[#00c627] text-[#00c327] text-[#00bd26] text-[#00b524] text-[#00af23] text-[#00aa22] text-[#00a721] text-[#00a621] text-[#00a621] text-[#00a621] text-[#00a721] text-[#00a922] text-[#00ae23] text-[#00b123] text-[#00b324] text-[#00b324] text-[#00b324] text-[#00b324] text-[#00b324]" type="submit" variant="accent">
                  Save Changes
                </Button>
              </div>
            )}
          </form>
        </Card>

        {/* Account Statistics */}
        <div className="grid grid-cols-1 md:grid-cols-3 gap-6">
          <Card className="text-center">
            <p className="mb-2 text-[#00af02]">Total Bookings</p>
            <p className="text-3xl font-bold text-[#00ad00]">8</p>
          </Card>
          <Card className="text-center">
            <p className="mb-2 text-[#ff0000]">Completed Services</p>
            <p className="text-3xl font-bold text-[#E63946]">5</p>
          </Card>
          <Card className="text-center">
            <p className="mb-2 text-[#1100fe]">Member Since</p>
            <p className="text-3xl font-bold text-[#457B9D]">2024</p>
          </Card>
        </div>
      </div>
    </DashboardLayout>
  );
}