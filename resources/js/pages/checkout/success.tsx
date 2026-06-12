import { Button } from '@/components/ui/button';
import StoreLayout from '@/layouts/store-layout';
import { Head, Link } from '@inertiajs/react';
import confetti from 'canvas-confetti';
import { BookMarked, Library } from 'lucide-react';
import { motion, useReducedMotion } from 'motion/react';
import { useEffect, useRef, useState } from 'react';

interface SuccessOrder {
    id: number;
    status: 'pending' | 'paid' | 'failed';
    total: string;
    books: { title: string | null; slug: string | null }[];
}

export default function CheckoutSuccess({ order }: { order: SuccessOrder | null }) {
    const titles = order?.books.map((book) => book.title).filter(Boolean) ?? [];
    const reduceMotion = useReducedMotion();
    const celebrated = useRef(false);
    const [flightTarget, setFlightTarget] = useState<{ x: number; y: number } | null>(null);

    useEffect(() => {
        if (celebrated.current || reduceMotion) {
            return;
        }
        celebrated.current = true;

        confetti({
            particleCount: 120,
            spread: 75,
            origin: { y: 0.6 },
            disableForReducedMotion: true,
        });

        // Fly the purchased book into the My Library nav link.
        const nav = document.getElementById('library-nav-link');
        if (nav) {
            const rect = nav.getBoundingClientRect();
            setFlightTarget({
                x: rect.left + rect.width / 2 - window.innerWidth / 2,
                y: rect.top + rect.height / 2 - window.innerHeight / 2,
            });
        }
    }, [reduceMotion]);

    return (
        <StoreLayout>
            <Head title="Thank you" />

            {flightTarget && (
                <motion.div
                    className="pointer-events-none fixed top-1/2 left-1/2 z-50"
                    initial={{ x: -24, y: -32, scale: 1, opacity: 1 }}
                    animate={{ x: flightTarget.x - 24, y: flightTarget.y - 32, scale: 0.15, opacity: 0 }}
                    transition={{ delay: 0.7, duration: 0.9, ease: [0.3, 0, 0.4, 1] }}
                    onAnimationComplete={() => setFlightTarget(null)}
                >
                    <div className="flex h-16 w-12 items-center justify-center rounded-r-md rounded-l-xs bg-primary text-primary-foreground shadow-lg">
                        <BookMarked className="size-5" />
                    </div>
                </motion.div>
            )}

            <div className="mx-auto flex max-w-xl flex-col items-center gap-6 py-16 text-center">
                <motion.h1
                    className="font-serif text-5xl font-semibold"
                    initial={reduceMotion ? false : { opacity: 0, scale: 0.8 }}
                    animate={{ opacity: 1, scale: 1 }}
                    transition={{ type: 'spring', stiffness: 200, damping: 15 }}
                >
                    Yours forever.
                </motion.h1>

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
