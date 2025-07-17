import { useEffect, useState } from 'react';
import { useParams } from 'react-router-dom';
import axios from 'axios';
import { CheckCircle, XCircle } from 'lucide-react';

export function PixPaymentPage() {
    const { token } = useParams<{ token: string }>();
    const [status, setStatus] = useState<'processing' | 'paid' | 'expired'>('processing');

    useEffect(() => {
        const processPayment = async () => {
            try {
                const response = await axios.get(`http://localhost:8000/api/pix/${token}`);
                setStatus(response.data.status);
            } catch (error) {
                console.error('Payment processing error:', error);
                setStatus('expired');
            }
        };

        // Simula um tempo de processamento
        setTimeout(() => {
            processPayment();
        }, 2000);
    }, [token]);

    return (
        <div className="min-h-screen flex items-center justify-center bg-background">
            <div className="text-center">
                {status === 'processing' && (
                    <div className="animate-spin rounded-full h-32 w-32 border-t-2 border-b-2 border-primary"></div>
                )}
                {status === 'paid' && (
                    <div className="flex flex-col items-center">
                        <CheckCircle className="h-32 w-32 text-green-500" />
                        <p className="text-2xl mt-4">Pagamento Confirmado!</p>
                    </div>
                )}
                {status === 'expired' && (
                    <div className="flex flex-col items-center">
                        <XCircle className="h-32 w-32 text-red-500" />
                        <p className="text-2xl mt-4">PIX Expirado!</p>
                    </div>
                )}
            </div>
        </div>
    );
} 