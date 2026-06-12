import { Button } from '@/components/ui/button';
import StoreLayout from '@/layouts/store-layout';
import { Head, Link } from '@inertiajs/react';
import { Library } from 'lucide-react';

interface SuccessOrder {
    id: number;
    status: 'pending' | 'paid' | 'failed';
    total: string;
    books: { title: string | null; slug: string | null }[];
}

export default function CheckoutSuccess({ order }: { order: SuccessOrder | null }) {
    const titles = order?.books.map((book) => book.title).filter(Boolean) ?? [];

    return (
        <StoreLayout>
            <Head title="Thank you" />

            <div className="mx-auto flex max-w-xl flex-col items-center gap-6 py-16 text-center">
                <h1 className="text-4xl font-semibold">Yours forever.</h1>

                {titles.length > 0 ? (
                    <p className="text-lg text-muted-foreground">
                        Thanks for your purchase — <span className="font-medium text-foreground">{titles.join(', ')}</span> is on its way to your
                        library.
                    </p>
                ) : (
                    <p className="text-lg text-muted-foreground">Thanks for your purchase!</p>
                )}

                {order?.status === 'pending' && (
                    <p className="text-sm text-muted-foreground">
                        We're confirming your payment with Stripe — your book will appear in your library in a moment.
                    </p>
                )}

                <div className="flex gap-3">
                    <Button asChild>
                        <Link href="/library">
                            <Library className="size-4" />
                            Go to My Library
                        </Link>
                    </Button>
                    <Button variant="outline" asChild>
                        <Link href="/">Keep browsing</Link>
                    </Button>
                </div>
            </div>
        </StoreLayout>
    );
}
