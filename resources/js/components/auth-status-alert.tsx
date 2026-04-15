import { CheckCircle2 } from 'lucide-react';
import { Alert, AlertDescription } from '@/components/ui/alert';

export default function AuthStatusAlert({ status }: { status?: string }) {
    if (!status) {
        return null;
    }

    return (
        <Alert variant="success" className="mb-4">
            <CheckCircle2 />
            <AlertDescription>{status}</AlertDescription>
        </Alert>
    );
}
