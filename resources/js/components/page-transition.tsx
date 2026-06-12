import { usePage } from '@inertiajs/react';
import { AnimatePresence, motion, useReducedMotion } from 'motion/react';
import { type PropsWithChildren } from 'react';

/**
 * Fade + slight slide on Inertia page changes. Keyed by pathname (not the
 * full URL) so query-string updates like search filters don't re-run the
 * transition. Disabled entirely under prefers-reduced-motion.
 */
export default function PageTransition({ children }: PropsWithChildren) {
    const { url } = usePage();
    const reduceMotion = useReducedMotion();

    if (reduceMotion) {
        return <>{children}</>;
    }

    return (
        <AnimatePresence mode="popLayout" initial={false}>
            <motion.div
                key={url.split('?')[0]}
                initial={{ opacity: 0, y: 10 }}
                animate={{ opacity: 1, y: 0 }}
                exit={{ opacity: 0, y: -10 }}
                transition={{ type: 'spring', stiffness: 300, damping: 30 }}
            >
                {children}
            </motion.div>
        </AnimatePresence>
    );
}
