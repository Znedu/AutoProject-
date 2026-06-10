import { useState } from 'react';
import { DashboardLayout } from '../../components/DashboardLayout';
import { Card } from '../../components/Card';
import { Button } from '../../components/Button';
import { Input, TextArea, Select } from '../../components/FormInputs';
import { FileText, Plus } from 'lucide-react';
import { showToast } from '../../utils/toast';

export default function ServiceNotes() {
  const [showAddForm, setShowAddForm] = useState(false);
  const [formData, setFormData] = useState({
    jobId: '',
    note: '',
  });

  const jobs = [
    { value: '', label: 'Select a job...' },
    { value: '1', label: 'Paint Job - Toyota Supra 2021' },
    { value: '2', label: 'Engine Customization - Honda Civic 2020' },
  ];

  const notes = [
    {
      id: 1,
      job: 'Paint Job - Toyota Supra 2021',
      note: 'Surface preparation completed. Starting primer application tomorrow. All rust spots have been treated.',
      date: 'March 30, 2026 - 3:45 PM',
      mechanic: 'You',
    },
    {
      id: 2,
      job: 'Paint Job - Toyota Supra 2021',
      note: 'Vehicle inspection completed. Beginning disassembly and masking. Minor dent on rear bumper needs attention.',
      date: 'March 29, 2026 - 10:15 AM',
      mechanic: 'You',
    },
    {
      id: 3,
      job: 'Turbo Installation - Subaru WRX 2022',
      note: 'Turbo installation completed. Performed test runs. All systems functioning optimally. Customer notified.',
      date: 'March 20, 2026 - 4:30 PM',
      mechanic: 'You',
    },
  ];

  const handleSubmit = (e: React.FormEvent) => {
    e.preventDefault();
    showToast.success('Service note added successfully!');
    setFormData({ jobId: '', note: '' });
    setShowAddForm(false);
  };

  return (
    <DashboardLayout role="mechanic">
      <div className="max-w-4xl mx-auto space-y-6">
        {/* Header */}
        <div className="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
          <div>
            <h1 className="text-3xl font-bold text-[#1F2937] mb-2">Service Notes</h1>
            <p className="text-gray-600">Track progress and add notes for your jobs.</p>
          </div>
          <Button
            variant="accent"
            onClick={() => setShowAddForm(!showAddForm)}
          >
            <Plus size={20} className="mr-2" />
            Add Note
          </Button>
        </div>

        {/* Add Note Form */}
        {showAddForm && (
          <Card>
            <h2 className="text-xl font-bold text-[#1F2937] mb-4">Add Service Note</h2>
            <form onSubmit={handleSubmit} className="space-y-4">
              <Select
                label="Select Job"
                options={jobs}
                value={formData.jobId}
                onChange={(e) => setFormData({ ...formData, jobId: e.target.value })}
                required
              />
              <TextArea
                label="Service Note"
                value={formData.note}
                onChange={(e) => setFormData({ ...formData, note: e.target.value })}
                placeholder="Describe what work was done, current progress, any issues found, etc..."
                rows={5}
                required
              />
              <div className="flex gap-3">
                <Button type="submit" variant="accent">
                  Add Note
                </Button>
                <Button
                  type="button"
                  variant="outline"
                  onClick={() => setShowAddForm(false)}
                >
                  Cancel
                </Button>
              </div>
            </form>
          </Card>
        )}

        {/* Notes History */}
        <div>
          <h2 className="text-xl font-bold text-[#1F2937] mb-4">Notes History</h2>
          <div className="space-y-4">
            {notes.map((note) => (
              <Card key={note.id}>
                <div className="flex items-start gap-4">
                  <div className="p-3 bg-[#E63946]/10 rounded-lg flex-shrink-0">
                    <FileText size={24} className="text-[#E63946]" />
                  </div>
                  <div className="flex-1">
                    <div className="flex flex-col sm:flex-row sm:items-start justify-between gap-2 mb-3">
                      <div>
                        <h3 className="font-bold text-[#1F2937] mb-1">{note.job}</h3>
                        <p className="text-sm text-gray-600">
                          {note.date} • By {note.mechanic}
                        </p>
                      </div>
                    </div>
                    <p className="text-gray-700 leading-relaxed">{note.note}</p>
                  </div>
                </div>
              </Card>
            ))}
          </div>
        </div>

        {/* Quick Tips */}
        <Card className="bg-[#457B9D]/10">
          <h3 className="font-bold text-[#1F2937] mb-3">💡 Tips for Service Notes</h3>
          <ul className="space-y-2 text-sm text-gray-700">
            <li>• Be specific about work completed and current status</li>
            <li>• Note any issues or concerns discovered during service</li>
            <li>• Include details that help track progress over time</li>
            <li>• Mention parts used or materials required</li>
            <li>• These notes are visible to customers for transparency</li>
          </ul>
        </Card>
      </div>
    </DashboardLayout>
  );
}