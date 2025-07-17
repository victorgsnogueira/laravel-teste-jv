import { useState, useEffect } from 'react';
import { useNavigate } from 'react-router-dom';
import { useAuth } from '../contexts/AuthContext';
import axios from 'axios';
import { toast } from 'react-hot-toast';
import { Dialog, DialogContent, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import { QRCodeSVG } from 'qrcode.react';

interface Pix {
    id: number;
    token: string;
    status: 'generated' | 'paid' | 'expired';
    amount: number;
    created_at: string;
    expires_at: string;
}

interface PaginatedPix {
    data: Pix[];
    current_page: number;
    last_page: number;
    total: number;
}

interface PixStats {
  generated: number;
  paid: number;
  expired: number;
}

export function Dashboard() {
  const [amount, setAmount] = useState('');
  const [isLoading, setIsLoading] = useState(false);
  const [stats, setStats] = useState<PixStats>({ generated: 0, paid: 0, expired: 0 });
  const [pixes, setPixes] = useState<PaginatedPix | null>(null);
  const [selectedPix, setSelectedPix] = useState<Pix | null>(null);
  const [isModalOpen, setIsModalOpen] = useState(false);
  const { logout } = useAuth();
  const navigate = useNavigate();

  useEffect(() => {
    fetchStats();
    fetchPixes(1);
    const interval = setInterval(fetchStats, 5000); // Atualiza a cada 5 segundos
    return () => clearInterval(interval);
  }, []);

  const fetchStats = async () => {
    try {
      const response = await axios.get('http://localhost:8000/api/pix/stats');
      setStats(response.data);
    } catch (error) {
      console.error('Error fetching stats:', error);
    }
  };

  const fetchPixes = async (page: number) => {
    try {
      const response = await axios.get(`http://localhost:8000/api/pix?page=${page}`);
      setPixes(response.data);
    } catch (error) {
        console.error('Error fetching pixes:', error);
    }
  }

  const handleCreatePix = async (e: React.FormEvent) => {
    e.preventDefault();
    setIsLoading(true);

    try {
      const response = await axios.post<{ data: Pix }>('http://localhost:8000/api/pix', {
        amount: parseFloat(amount),
      });

      const newPix = response.data.data;
      setSelectedPix(newPix);
      setIsModalOpen(true);
      toast.success('PIX gerado com sucesso!');
      setAmount('');
      fetchStats();
    } catch (error) {
      console.error('Error creating PIX:', error);
      toast.error('Erro ao gerar PIX. Tente novamente.');
    } finally {
      setIsLoading(false);
    }
  };

  const handleLogout = async () => {
    await logout();
    navigate('/login');
  };

  return (
    <div className="min-h-screen bg-background">
      <nav className="bg-card shadow-sm">
        <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
          <div className="flex justify-between h-16">
            <div className="flex items-center">
              <h1 className="text-2xl font-bold text-foreground">Dashboard</h1>
            </div>
            <div className="flex items-center">
              <button
                onClick={handleLogout}
                className="ml-4 px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-primary-foreground bg-primary hover:bg-primary/90 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary"
              >
                Sair
              </button>
            </div>
          </div>
        </div>
      </nav>

      <main className="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
        <div className="px-4 py-6 sm:px-0">
          <div className="grid grid-cols-1 gap-6 sm:grid-cols-3">
            <div className="bg-card p-6 rounded-lg shadow">
              <h3 className="text-lg font-medium text-foreground">PIX Gerados</h3>
              <p className="mt-2 text-3xl font-bold text-primary">{stats.generated}</p>
            </div>
            <div className="bg-card p-6 rounded-lg shadow">
              <h3 className="text-lg font-medium text-foreground">PIX Pagos</h3>
              <p className="mt-2 text-3xl font-bold text-green-600">{stats.paid}</p>
            </div>
            <div className="bg-card p-6 rounded-lg shadow">
              <h3 className="text-lg font-medium text-foreground">PIX Expirados</h3>
              <p className="mt-2 text-3xl font-bold text-destructive">{stats.expired}</p>
            </div>
          </div>

          <div className="mt-8">
            <div className="bg-card p-6 rounded-lg shadow">
              <h3 className="text-lg font-medium text-foreground mb-4">Gerar Novo PIX</h3>
              <form onSubmit={handleCreatePix} className="space-y-4">
                <div>
                  <label htmlFor="amount" className="block text-sm font-medium text-foreground">
                    Valor
                  </label>
                  <div className="mt-1 relative rounded-md shadow-sm">
                    <div className="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                      <span className="text-muted-foreground sm:text-sm">R$</span>
                    </div>
                    <input
                      type="number"
                      name="amount"
                      id="amount"
                      step="0.01"
                      min="0.01"
                      value={amount}
                      onChange={(e) => setAmount(e.target.value)}
                      className="block w-full pl-10 pr-12 sm:text-sm border border-input rounded-md bg-background px-3 py-2 placeholder:text-muted-foreground focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary"
                      placeholder="0.00"
                      required
                    />
                  </div>
                </div>
                <button
                  type="submit"
                  disabled={isLoading}
                  className="w-full py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-primary-foreground bg-primary hover:bg-primary/90 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary disabled:opacity-50 disabled:cursor-not-allowed"
                >
                  {isLoading ? 'Gerando...' : 'Gerar PIX'}
                </button>
              </form>
            </div>
          </div>

          <div className="mt-8">
            <h3 className="text-lg font-medium text-foreground mb-4">Histórico de PIX</h3>
            <div className="bg-card shadow overflow-hidden sm:rounded-md">
              <ul className="divide-y divide-border">
                {pixes?.data.map((pix) => (
                  <li key={pix.id}>
                    <div className="px-4 py-4 sm:px-6">
                      <div className="flex items-center justify-between">
                        <p className="text-sm font-medium text-primary truncate">
                          R$ {pix.amount.toFixed(2)}
                        </p>
                        <div className="ml-2 flex-shrink-0 flex">
                          <p className={`px-2 inline-flex text-xs leading-5 font-semibold rounded-full ${
                              pix.status === 'paid' ? 'bg-green-100 text-green-800' : 
                              pix.status === 'expired' ? 'bg-red-100 text-red-800' : 
                              'bg-yellow-100 text-yellow-800'
                          }`}>
                            {pix.status}
                          </p>
                        </div>
                      </div>
                      <div className="mt-2 sm:flex sm:justify-between">
                        <div className="sm:flex">
                          <p className="flex items-center text-sm text-muted-foreground">
                            Criado em: {new Date(pix.created_at).toLocaleString()}
                          </p>
                          <p className="mt-2 flex items-center text-sm text-muted-foreground sm:mt-0 sm:ml-6">
                            Expira em: {new Date(pix.expires_at).toLocaleString()}
                          </p>
                        </div>
                      </div>
                    </div>
                  </li>
                ))}
              </ul>
            </div>
            {/* Paginação */}
            {pixes && (
              <div className="mt-4 flex justify-between">
                <button
                  onClick={() => fetchPixes(pixes.current_page - 1)}
                  disabled={pixes.current_page === 1}
                  className="px-4 py-2 text-sm font-medium text-white bg-primary rounded-md disabled:opacity-50"
                >
                  Anterior
                </button>
                <button
                  onClick={() => fetchPixes(pixes.current_page + 1)}
                  disabled={pixes.current_page === pixes.last_page}
                  className="px-4 py-2 text-sm font-medium text-white bg-primary rounded-md disabled:opacity-50"
                >
                  Próxima
                </button>
              </div>
            )}
          </div>
        </div>
      </main>

      {selectedPix && (
        <Dialog open={isModalOpen} onOpenChange={setIsModalOpen}>
          <DialogContent>
            <DialogHeader>
              <DialogTitle>PIX Gerado</DialogTitle>
            </DialogHeader>
            <div className="flex flex-col items-center justify-center space-y-4">
                <QRCodeSVG value={`${window.location.origin}/pix/${selectedPix.token}`} size={256} />
                <p>Status: <span className="font-bold">{selectedPix.status}</span></p>
                <p>Expira em: {new Date(selectedPix.expires_at).toLocaleTimeString()}</p>
                <a 
                    href={`/pix/${selectedPix.token}`} 
                    target="_blank" 
                    rel="noopener noreferrer"
                    className="w-full py-2 px-4 text-center border border-transparent rounded-md shadow-sm text-sm font-medium text-primary-foreground bg-primary hover:bg-primary/90"
                >
                    Abrir Link de Pagamento
                </a>
            </div>
          </DialogContent>
        </Dialog>
      )}
    </div>
  );
} 