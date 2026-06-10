import { useState } from 'react';
import { DashboardLayout } from '../../components/DashboardLayout';
import { Card } from '../../components/Card';
import { Button } from '../../components/Button';
import { TextArea } from '../../components/FormInputs';
import { StatusBadge } from '../../components/StatusBadge';
import { MessageSquare, Send, CheckCircle2 } from 'lucide-react';
import { showToast } from '../../utils/toast';

export default function CustomerAssistance() {
  const [selectedTicket, setSelectedTicket] = useState<number | null>(null);
  const [replyMessage, setReplyMessage] = useState('');

  const tickets = [
    {
      id: 1,
      customer: 'Juan Dela Cruz',
      subject: 'Question about paint warranty',
      message: 'I would like to know more about the warranty coverage for the paint job.',
      status: 'open' as const,
      date: 'March 30, 2026',
      replies: [],
    },
    {
      id: 2,
      customer: 'Maria Santos',
      subject: 'Reschedule booking request',
      message: 'I need to reschedule my April 5 appointment to April 8. Is this possible?',
      status: 'in-progress' as const,
      date: 'March 28, 2026',
      replies: [
        {
          from: 'Staff',
          message: 'Hello Maria, we can accommodate your request. I will check the availability for April 8.',
          date: 'March 28, 2026 - 2:30 PM',
        },
        {
          from: 'Customer',
          message: 'Thank you! I appreciate your help.',
          date: 'March 28, 2026 - 3:15 PM',
        },
      ],
    },
    {
      id: 3,
      customer: 'Pedro Lopez',
      subject: 'Invoice inquiry',
      message: 'Can I get a detailed breakdown of the service costs for my turbo installation?',
      status: 'resolved' as const,
      date: 'March 25, 2026',
      replies: [
        {
          from: 'Staff',
          message: 'Hello Pedro, I have attached the detailed invoice to your account. You can download it from your booking page.',
          date: 'March 25, 2026 - 11:00 AM',
        },
        {
          from: 'Customer',
          message: 'Perfect, thank you!',
          date: 'March 25, 2026 - 11:30 AM',
        },
      ],
    },
  ];

  const handleSendReply = (ticketId: number) => {
    if (!replyMessage.trim()) {
      showToast.error('Please enter a reply message');
      return;
    }
    showToast.success(`Reply sent to ticket #${ticketId}`);
    setReplyMessage('');
  };

  const handleResolveTicket = (ticketId: number) => {
    showToast.success(`Ticket #${ticketId} marked as resolved`);
  };

  const selectedTicketData = tickets.find(t => t.id === selectedTicket);

  return (
    <DashboardLayout role="staff">
      <div className="space-y-6">
        {/* Header */}
        <div>
          <h1 className="text-3xl font-bold text-[#1F2937] mb-2">Customer Assistance</h1>
          <p className="text-gray-600">Respond to customer support tickets and inquiries.</p>
        </div>

        {/* Filters */}
        <Card>
          <div className="flex flex-wrap gap-2">
            <Button variant="primary" size="sm">All Tickets</Button>
            <Button variant="ghost" size="sm">Open</Button>
            <Button variant="ghost" size="sm">In Progress</Button>
            <Button variant="ghost" size="sm">Resolved</Button>
          </div>
        </Card>

        {/* Two Column Layout */}
        <div className="grid grid-cols-1 lg:grid-cols-3 gap-6">
          {/* Tickets List */}
          <div className="lg:col-span-1 space-y-3">
            <h2 className="text-lg font-bold text-[#1F2937] mb-3">Support Tickets</h2>
            {tickets.map((ticket) => (
              <Card
                key={ticket.id}
                className={`cursor-pointer transition-all ${
                  selectedTicket === ticket.id ? 'ring-2 ring-[#E63946]' : ''
                }`}
                onClick={() => setSelectedTicket(ticket.id)}
              >
                <div className="space-y-2">
                  <div className="flex items-start justify-between gap-2">
                    <h3 className="font-bold text-[#1F2937] text-sm">{ticket.subject}</h3>
                    <StatusBadge status={ticket.status}>
                      {ticket.status === 'open' ? 'Open' : ticket.status === 'in-progress' ? 'In Progress' : 'Resolved'}
                    </StatusBadge>
                  </div>
                  <p className="text-xs text-gray-600">{ticket.customer}</p>
                  <p className="text-xs text-gray-500">{ticket.date}</p>
                </div>
              </Card>
            ))}
          </div>

          {/* Ticket Details & Reply */}
          <div className="lg:col-span-2">
            {selectedTicketData ? (
              <div className="space-y-6">
                {/* Ticket Info */}
                <Card>
                  <div className="flex items-start justify-between mb-4">
                    <div>
                      <h2 className="text-xl font-bold text-[#1F2937] mb-2">
                        {selectedTicketData.subject}
                      </h2>
                      <p className="text-sm text-gray-600">
                        Ticket #{selectedTicketData.id} • {selectedTicketData.customer} • {selectedTicketData.date}
                      </p>
                    </div>
                    <StatusBadge status={selectedTicketData.status}>
                      {selectedTicketData.status === 'open' ? 'Open' : 
                       selectedTicketData.status === 'in-progress' ? 'In Progress' : 'Resolved'}
                    </StatusBadge>
                  </div>
                  <div className="bg-gray-50 rounded-lg p-4">
                    <p className="text-[#1F2937]">{selectedTicketData.message}</p>
                  </div>
                </Card>

                {/* Conversation Thread */}
                {selectedTicketData.replies.length > 0 && (
                  <Card>
                    <h3 className="font-bold text-[#1F2937] mb-4">Conversation</h3>
                    <div className="space-y-4">
                      {selectedTicketData.replies.map((reply, index) => (
                        <div
                          key={index}
                          className={`p-4 rounded-lg ${
                            reply.from === 'Staff' ? 'bg-[#457B9D]/10' : 'bg-gray-50'
                          }`}
                        >
                          <div className="flex items-center justify-between mb-2">
                            <span className="font-medium text-[#1F2937]">{reply.from}</span>
                            <span className="text-xs text-gray-600">{reply.date}</span>
                          </div>
                          <p className="text-gray-700">{reply.message}</p>
                        </div>
                      ))}
                    </div>
                  </Card>
                )}

                {/* Reply Form */}
                {selectedTicketData.status !== 'resolved' && (
                  <Card>
                    <h3 className="font-bold text-[#1F2937] mb-4">Send Reply</h3>
                    <div className="space-y-4">
                      <TextArea
                        value={replyMessage}
                        onChange={(e) => setReplyMessage(e.target.value)}
                        placeholder="Type your response to the customer..."
                        rows={6}
                      />
                      <div className="flex gap-3">
                        <Button
                          variant="accent"
                          onClick={() => handleSendReply(selectedTicketData.id)}
                        >
                          <Send size={16} className="mr-2" />
                          Send Reply
                        </Button>
                        <Button
                          variant="secondary"
                          onClick={() => handleResolveTicket(selectedTicketData.id)}
                        >
                          Mark as Resolved
                        </Button>
                      </div>
                    </div>
                  </Card>
                )}

                {selectedTicketData.status === 'resolved' && (
                  <Card className="bg-green-50">
                    <div className="flex items-center gap-3">
                      <div className="p-2 bg-green-500 rounded-full">
                        <Send size={20} className="text-white" />
                      </div>
                      <div>
                        <p className="font-medium text-green-800">This ticket has been resolved</p>
                        <p className="text-sm text-green-600">The customer has been notified.</p>
                      </div>
                    </div>
                  </Card>
                )}
              </div>
            ) : (
              <Card className="h-full flex items-center justify-center">
                <div className="text-center text-gray-500">
                  <p>Select a ticket to view details and respond</p>
                </div>
              </Card>
            )}
          </div>
        </div>
      </div>
    </DashboardLayout>
  );
}