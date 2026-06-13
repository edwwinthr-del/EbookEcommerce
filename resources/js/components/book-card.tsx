import { Link } from '@inertiajs/react';
import { motion, useMotionValue, useReducedMotion, useSpring, useTransform } from 'motion/react';
import { type CSSProperties, type PointerEvent } from 'react';

export interface CatalogBook {
    id: number;
    title: string;
    author: string;
    slug: string;
    price: string;
    accent_color: string | null;
    cover_url: string | null;
    category: { name: string; slug: string } | null;
}

export function BookCover({ book, className = '' }: { book: CatalogBook; className?: string }) {
    if (book.cover_url) {
        return <img src={book.cover_url} alt={`Cover of ${book.title}`} className={`h-full w-full object-cover ${className}`} />;
    }

    return (
        <div
            className={`flex h-full w-full items-center justify-center p-4 text-center ${className}`}
            style={{ backgroundColor: book.accent_color ?? '#6366f1' }}
        >
            <span className="font-serif text-lg font-semibold text-white [text-shadow:0_1px_2px_rgba(0,0,0,0.4)]">{book.title}</span>
        </div>
    );
}

/**
 * 3D book: CSS perspective + pseudo-element spine (via --book-accent),
 * spring-tilting toward the cursor on hover. Decorative motion is
 * disabled when the user prefers reduced motion.
 */
export function Book3D({ book, maxTilt = 14 }: { book: CatalogBook; maxTilt?: number }) {
    const reduceMotion = useReducedMotion();

    const pointerX = useMotionValue(0);
    const pointerY = useMotionValue(0);

    const rotateY = useSpring(useTransform(pointerX, [-0.5, 0.5], [-maxTilt, maxTilt]), { stiffness: 250, damping: 18 });
    const rotateX = useSpring(useTransform(pointerY, [-0.5, 0.5], [maxTilt * 0.6, -maxTilt * 0.6]), { stiffness: 250, damping: 18 });

    const onPointerMove = (event: PointerEvent<HTMLDivElement>) => {
        if (reduceMotion) {
            return;
        }

        const rect = event.currentTarget.getBoundingClientRect();
        pointerX.set((event.clientX - rect.left) / rect.width - 0.5);
        pointerY.set((event.clientY - rect.top) / rect.height - 0.5);
    };

    const onPointerLeave = () => {
        pointerX.set(0);
        pointerY.set(0);
    };

    return (
        <div className="book-3d-scene" onPointerMove={onPointerMove} onPointerLeave={onPointerLeave}>
            <motion.div
                className="book-3d aspect-2/3 overflow-hidden rounded-r-md rounded-l-xs shadow-md"
                style={{ rotateX: reduceMotion ? 0 : rotateX, rotateY: reduceMotion ? 0 : rotateY, '--book-accent': book.accent_color ?? '#6366f1' } as CSSProperties}
            >
                <BookCover book={book} />
            </motion.div>
        </div>
    );
}

export default function BookCard({ book }: { book: CatalogBook }) {
    const reduceMotion = useReducedMotion();
    // Deterministic playful rotation between -3deg and 3deg.
    const restRotation = reduceMotion ? 0 : ((book.id * 37) % 7) - 3;

    return (
        <Link href={`/books/${book.slug}`} className="group flex flex-col gap-3">
            <motion.div
                style={{ rotate: restRotation }}
                whileHover={reduceMotion ? undefined : { rotate: 0, scale: 1.03 }}
                transition={{ type: 'spring', stiffness: 300, damping: 20 }}
            >
                <Book3D book={book} />
            </motion.div>
            <div>
                <h3 className="font-serif font-medium leading-tight group-hover:underline">{book.title}</h3>
                <p className="text-sm text-muted-foreground">{book.author}</p>
                <p className="mt-1 text-sm font-semibold">{parseFloat(book.price) === 0 ? 'Free' : `$${book.price}`}</p>
            </div>
        </Link>
    );
}
