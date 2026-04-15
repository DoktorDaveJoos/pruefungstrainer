import { Checkbox } from '@/components/ui/checkbox';
import { Label } from '@/components/ui/label';

export const ANSWER_OPTION_BASE =
    'flex cursor-pointer items-start gap-3 rounded-md border p-4 shadow-xs';

export function AnswerOption({
    id,
    text,
    checked,
    onCheckedChange,
}: {
    id: string | number;
    text: string;
    checked: boolean;
    onCheckedChange: () => void;
}) {
    const inputId = `option-${id}`;

    return (
        <Label
            htmlFor={inputId}
            className={`${ANSWER_OPTION_BASE} border-border hover:bg-muted`}
        >
            <Checkbox
                id={inputId}
                checked={checked}
                onCheckedChange={onCheckedChange}
            />
            <span className="text-base leading-relaxed">{text}</span>
        </Label>
    );
}
