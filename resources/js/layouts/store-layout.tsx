import { Button } from '@/components/ui/button';
import { type SharedData } from '@/types';
import { Link, usePage } from '@inertiajs/react';
import { BookOpen, Library } from 'lucide-react';
import { type PropsWithChildren } from 'react';

export default function StoreLayout({ children }: PropsWithChildren) {
    const { auth } = usePage<SharedData>().props;

    return (
        <div className="min-h-screen bg-background text-foreground">
            <header className="border-b">
                <div className="mx-auto flex max-w-6xl items-center justify-between gap-4 px-4 py-4">
                    <Link href="/" className="flex items-center gap-2 text-lg font-semibold">
                        <BookOpen className="size-5" />
                        E-Book Store
                    </Link>

                    <nav className="flex items-center gap-2">
                        {auth.user ? (
                            <>
                                <Button variant="ghost" asChild>
                                    <Link href="/library">
                                        <Library className="size-4" />
                                        My Library
                                    </Link>
                                </Button>
                                <Button variant="outline" asChild>
                                    <Link href="/dashboard">Dashboard</Link>
                                </Button>
                            </>
                        ) : (
                            <>
                                <Button variant="ghost" asChild>
                                    <Link href="/login">Log in</Link>
                                </Button>
                                <Button asChild>
                                    <Link href="/register">Register</Link>
                                </Button>
                            </>
                        )}
                    </nav>
                </div>
            </header>

            <main className="mx-auto max-w-6xl px-4 py-8">{children}</main>
        </div>
    );
}
