import { toast } from 'sonner';

export const showToast = {
  success: (message: string) => {
    toast.success(message, {
      style: {
        background: '#10B981',
        color: 'white',
        border: 'none',
      },
      duration: 3000,
    });
  },
  
  error: (message: string) => {
    toast.error(message, {
      style: {
        background: '#E63946',
        color: 'white',
        border: 'none',
      },
      duration: 3000,
    });
  },
  
  info: (message: string) => {
    toast.info(message, {
      style: {
        background: '#457B9D',
        color: 'white',
        border: 'none',
      },
      duration: 3000,
    });
  },
};
