import { useState, useMemo, useEffect } from 'react';
import { useLocation } from 'react-router';
import { DashboardLayout } from '../../components/DashboardLayout';
import { Card } from '../../components/Card';
import { Button } from '../../components/Button';
import { Input, TextArea } from '../../components/FormInputs';
import { StatusBadge } from '../../components/StatusBadge';
import { MessageSquare, Plus, Clock, Paperclip, ArrowLeft, Send } from 'lucide-react';
import { showToast } from '../../utils/toast';

interface TicketReply {
  id: number;
  author: string;
  role: 'customer' | 'staff';
  message: string;
  date: string;
  time: string;
}

export default function SupportTickets() {
  const location = useLocation();
  const [showCreateForm, setShowCreateForm] = useState(false);
  const [selectedFilter, setSelectedFilter] = useState<'all' | 'open' | 'in-progress' | 'resolved'>('all');
  const [viewingTicket, setViewingTicket] = useState<number | null>(null);
  const [replyMessage, setReplyMessage] = useState('');
  const [formData, setFormData] = useState({
    subject: '',
    message: '',
  });

  // Handle prefilled data from navigation
  useEffect(() => {
    if (location.state?.prefilledSubject) {
      setFormData({
        subject: location.state.prefilledSubject,
        message: location.state.prefilledMessage || '',
      });
      setShowCreateForm(true);
    }
  }, [location.state]);

  const [ticketReplies, setTicketReplies] = useState<Record<number, TicketReply[]>>({
    2: [
      {
        id: 1,
        author: 'Customer Support',
        role: 'staff',
        message: 'Thank you for contacting us. We can help you reschedule your appointment. What date works best for you?',
        date: 'March 28, 2026',
        time: '2:30 PM',
      },
      {
        id: 2,
        author: 'You',
        role: 'customer',
        message: 'April 8 at 10:00 AM would be perfect. Is that available?',
        date: 'March 28, 2026',
        time: '3:15 PM',
      },
    ],
    3: [
      {
        id: 1,
        author: 'Billing Team',
        role: 'staff',
        message: 'Here is the breakdown: Parts - ₱35,000, Labor - ₱15,000, Materials - ₱5,000. Total: ₱55,000',
        date: 'March 25, 2026',
        time: '11:00 AM',
      },
      {
        id: 2,
        author: 'You',
        role: 'customer',
        message: 'Thank you for the detailed breakdown!',
        date: 'March 25, 2026',
        time: '2:00 PM',
      },
      {
        id: 3,
        author: 'Billing Team',
        role: 'staff',
        message: 'You\'re welcome! Feel free to reach out if you have any other questions.',
        date: 'March 25, 2026',
        time: '2:15 PM',
      },
    ],
  });

  const tickets = [
    {
      id: 1,
      subject: 'Question about paint warranty',
      message: 'I would like to know more about the warranty coverage for the paint job.',
      status: 'open' as const,
      date: 'March 30, 2026',
      replies: 0,
    },
    {
      id: 2,
      subject: 'Reschedule booking request',
      message: 'I need to reschedule my April 5 appointment to April 8.',
      status: 'in-progress' as const,
      date: 'March 28, 2026',
      replies: 2,
    },
    {
      id: 3,
      subject: 'Invoice inquiry',
      message: 'Can I get a detailed breakdown of the service costs?',
      status: 'resolved' as const,
      date: 'March 25, 2026',
      replies: 3,
    },
  ];

  const handleSubmit = (e: React.FormEvent) => {
    e.preventDefault();
    showToast.success('Support ticket created successfully!');
    setFormData({ subject: '', message: '' });
    setShowCreateForm(false);
  };

  const handleViewTicket = (ticketId: number) => {
    setViewingTicket(ticketId);
    setReplyMessage('');
  };

  const handleBackToList = () => {
    setViewingTicket(null);
    setReplyMessage('');
  };

  const handleSubmitReply = (e: React.FormEvent) => {
    e.preventDefault();
    if (!viewingTicket || !replyMessage.trim()) return;

    const newReply: TicketReply = {
      id: (ticketReplies[viewingTicket]?.length || 0) + 1,
      author: 'You',
      role: 'customer',
      message: replyMessage,
      date: new Date().toLocaleDateString('en-US', { year: 'numeric', month: 'long', day: 'numeric' }),
      time: new Date().toLocaleTimeString('en-US', { hour: 'numeric', minute: '2-digit' }),
    };

    setTicketReplies({
      ...ticketReplies,
      [viewingTicket]: [...(ticketReplies[viewingTicket] || []), newReply],
    });

    setReplyMessage('');
    showToast.success('Reply sent successfully!');
  };

  // Filter tickets based on selected filter
  const filteredTickets = useMemo(() => {
    if (selectedFilter === 'all') return tickets;
    return tickets.filter((ticket) => ticket.status === selectedFilter);
  }, [selectedFilter]);

  const currentTicket = tickets.find(t => t.id === viewingTicket);
  const currentReplies = viewingTicket ? ticketReplies[viewingTicket] || [] : [];

  // If viewing a ticket, show the ticket detail view
  if (viewingTicket && currentTicket) {
    return (
      <DashboardLayout role="customer">
        <div className="max-w-4xl mx-auto space-y-6">
          {/* Back Button */}
          <Button
            variant="ghost"
            onClick={handleBackToList}
            className="mb-4"
          >
            <ArrowLeft size={20} className="mr-2" />
            Back to Tickets
          </Button>

          {/* Ticket Header */}
          <Card>
            <div className="flex items-start justify-between gap-4 mb-4">
              <div className="flex-1">
                <h1 className="text-2xl font-bold mb-2 text-gray-900 dark:text-white">
                  {currentTicket.subject}
                </h1>
                <div className="flex flex-wrap items-center gap-3 text-sm text-gray-600 dark:text-gray-400">
                  <span>Ticket #{currentTicket.id}</span>
                  <span>•</span>
                  <span>{currentTicket.date}</span>
                </div>
              </div>
              <StatusBadge status={currentTicket.status}>
                {currentTicket.status === 'open' ? 'Open' : currentTicket.status === 'in-progress' ? 'In Progress' : 'Resolved'}
              </StatusBadge>
            </div>

            {/* Original Message */}
            <div className="p-4 bg-gray-50 dark:bg-gray-800 rounded-lg">
              <div className="flex items-center gap-2 mb-2">
                <span className="font-semibold text-gray-900 dark:text-white">You</span>
                <span className="text-sm text-gray-600 dark:text-gray-400">opened this ticket</span>
              </div>
              <p className="text-gray-700 dark:text-gray-300">{currentTicket.message}</p>
            </div>
          </Card>

          {/* Replies */}
          {currentReplies.length > 0 && (
            <div className="space-y-4">
              <h2 className="text-xl font-bold text-gray-900 dark:text-white">Replies</h2>
              {currentReplies.map((reply) => (
                <Card key={reply.id}>
                  <div className="flex items-start gap-4">
                    <div className={`w-10 h-10 rounded-full flex items-center justify-center ${
                      reply.role === 'staff'
                        ? 'bg-[#E63946] text-white'
                        : 'bg-gray-300 dark:bg-gray-600 text-gray-700 dark:text-white'
                    }`}>
                      {reply.author.charAt(0).toUpperCase()}
                    </div>
                    <div className="flex-1">
                      <div className="flex items-center gap-2 mb-2">
                        <span className="font-semibold text-gray-900 dark:text-white">{reply.author}</span>
                        <span className="text-sm text-gray-600 dark:text-gray-400">
                          {reply.date} at {reply.time}
                        </span>
                      </div>
                      <p className="text-gray-700 dark:text-gray-300">{reply.message}</p>
                    </div>
                  </div>
                </Card>
              ))}
            </div>
          )}

          {/* Reply Form */}
          {currentTicket.status !== 'resolved' && (
            <Card>
              <h3 className="text-lg font-bold mb-4 text-gray-900 dark:text-white">Add Reply</h3>
              <form onSubmit={handleSubmitReply} className="space-y-4">
                <TextArea
                  value={replyMessage}
                  onChange={(e) => setReplyMessage(e.target.value)}
                  placeholder="Type your reply here..."
                  rows={4}
                  required
                />
                <div className="flex gap-3">
                  <Button type="submit" variant="accent" className="bg-green-600 hover:bg-green-700 text-white">
                    <Send size={16} className="mr-2" />
                    Send Reply
                  </Button>
                  <Button
                    type="button"
                    variant="outline"
                    className="bg-red-600 hover:bg-red-700 text-white border-red-600"
                    onClick={() => setReplyMessage('')}
                  >
                    Clear
                  </Button>
                </div>
              </form>
            </Card>
          )}

          {currentTicket.status === 'resolved' && (
            <Card>
              <div className="text-center py-6">
                <p className="text-gray-600 dark:text-gray-400">
                  This ticket has been resolved and is now closed.
                </p>
              </div>
            </Card>
          )}
        </div>
      </DashboardLayout>
    );
  }

  return (
    <DashboardLayout role="customer">
      <div className="max-w-4xl mx-auto space-y-6">
        {/* Header */}
        <div className="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
          <div>
            <h1 className="text-3xl font-bold mb-2 text-gray-900 dark:text-white">Support Tickets</h1>
            <p className="text-gray-600 dark:text-gray-400">Get help with your bookings and services.</p>
          </div>
          <Button
            variant="accent"
            onClick={() => setShowCreateForm(!showCreateForm)}
          >
            <Plus size={20} className="mr-2" />
            New Ticket
          </Button>
        </div>

        {/* Create Ticket Form */}
        {showCreateForm && (
          <Card>
            <h2 className="text-xl font-bold text-gray-900 dark:text-white mb-4">Create Support Ticket</h2>
            <form onSubmit={handleSubmit} className="space-y-4">
              <Input
                label="Subject"
                value={formData.subject}
                onChange={(e) => setFormData({ ...formData, subject: e.target.value })}
                placeholder="Brief description of your issue"
                required
              />
              <TextArea
                label="Message"
                value={formData.message}
                onChange={(e) => setFormData({ ...formData, message: e.target.value })}
                placeholder="Provide detailed information about your concern..."
                rows={5}
                required
              />
              <div>
                <label className="block mb-2 text-gray-900 dark:text-white">
                  Attach Image (Optional)
                </label>
                <button
                  type="button"
                  className="flex items-center gap-2 px-4 py-2 border-2 border-dashed border-gray-300 rounded-lg hover:border-[#E63946] transition-colors"
                >
                  <Paperclip size={20} />
                  <span>Choose file...</span>
                </button>
              </div>
              <div className="flex gap-3">
                <Button type="submit" variant="accent" className="bg-green-600 hover:bg-green-700 text-white">
                  Submit Ticket
                </Button>
                <Button
                  type="button"
                  variant="outline"
                  className="bg-red-600 hover:bg-red-700 text-white border-red-600"
                  onClick={() => {
                    setShowCreateForm(false);
                    setFormData({ subject: '', message: '' });
                  }}
                >
                  Cancel
                </Button>
              </div>
            </form>
          </Card>
        )}

        {/* Filters */}
        <Card>
          <div className="flex flex-wrap gap-2">
            <Button
              variant={selectedFilter === 'all' ? 'primary' : 'ghost'}
              size="sm"
              onClick={() => setSelectedFilter('all')}
            >
              All Tickets
            </Button>
            <Button
              variant={selectedFilter === 'open' ? 'primary' : 'ghost'}
              size="sm"
              onClick={() => setSelectedFilter('open')}
            >
              Open
            </Button>
            <Button
              variant={selectedFilter === 'in-progress' ? 'primary' : 'ghost'}
              size="sm"
              onClick={() => setSelectedFilter('in-progress')}
            >
              In Progress
            </Button>
            <Button
              variant={selectedFilter === 'resolved' ? 'primary' : 'ghost'}
              size="sm"
              onClick={() => setSelectedFilter('resolved')}
            >
              Resolved
            </Button>
          </div>
        </Card>

        {/* Tickets List */}
        <div className="space-y-4">
          {filteredTickets.length === 0 ? (
            <Card>
              <div className="text-center py-8">
                <p className="text-gray-600 dark:text-gray-400">No tickets found for this filter.</p>
              </div>
            </Card>
          ) : (
            filteredTickets.map((ticket) => (
              <Card key={ticket.id} hover className="cursor-pointer">
                <div className="flex flex-col gap-4">
                  <div className="flex items-start justify-between gap-4">
                    <div className="flex-1">
                      <h3 className="text-lg font-bold mb-2 text-gray-900 dark:text-white">
                        {ticket.subject}
                      </h3>
                      <p className="mb-3 text-gray-700 dark:text-gray-300">{ticket.message}</p>
                      <div className="flex flex-wrap items-center gap-3 text-sm text-gray-600 dark:text-gray-400">
                        <span>Ticket #{ticket.id}</span>
                        <span>•</span>
                        <span>{ticket.date}</span>
                        <span>•</span>
                        <span>{ticket.replies} {ticket.replies === 1 ? 'reply' : 'replies'}</span>
                      </div>
                    </div>
                  <StatusBadge status={ticket.status}>
                    {ticket.status === 'open' ? 'Open' : ticket.status === 'in-progress' ? 'In Progress' : 'Resolved'}
                  </StatusBadge>
                </div>
                  <div>
                    <Button
                      variant="secondary"
                      size="sm"
                      onClick={() => handleViewTicket(ticket.id)}
                    >
                      {ticket.status === 'resolved' ? 'View Details' : 'View & Reply'}
                    </Button>
                  </div>
                </div>
              </Card>
            ))
          )}
        </div>
      </div>
    </DashboardLayout>
  );
}