import { useState } from '@wordpress/element';
import { useDispatch, useSelect } from "@wordpress/data";
import { registerPlugin } from "@wordpress/plugins";
import { PluginDocumentSettingPanel } from "@wordpress/edit-post";
import { TextareaControl, Button } from '@wordpress/components';
import "./style.css";

const getText = (html) => {
    return html.replace(/<\/?[^>]+(>|$)/g, "");
};

const PluginDocumentSettingPanelDemo = () => {
    const [question, setQuestion] = useState('');
    const [answer, setAnswer] = useState('');

    const postContent = useSelect(
        (select) => select("core/editor").getEditedPostAttribute("content"),
        [],
    );

    const postText = getText(postContent);
    const trimmed = postText.replace(/\s+/g, " ");
    const final = trimmed.replace(/<br>/g, "\n");

    const { editPost } = useDispatch("core/editor");

    const handleNewTitle = () => {
        const newTitle = final.slice(0, 50);
        editPost({ title: newTitle });
    };

    const callAIChat = async () => {
        try {
            // Obtener el nonce desde algún lugar de tu página, por ejemplo, desde un campo oculto.
            const nonce = document.querySelector('#my_nonce_field').value;

            const response = await fetch('/wp-json/plugin/v1/chatgpt/', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-WP-Nonce': nonce,
                },
                body: JSON.stringify({ question }),
            });

            if (!response.ok) {
                throw new Error(`Error: ${response.statusText}`);
            }

            const data = await response.json();
            setAnswer(data.answer);
        } catch (error) {
            console.error('Error:', error);
            setAnswer('Error al obtener la respuesta.');
        }
    };

    return (
        <PluginDocumentSettingPanel
            name="plugin-document-setting-panel-demo"
            title="Generate Title"
            className="plugin-document-setting-panel-demo"
        >
            <TextareaControl
                label="Tu pregunta para ChatGPT:"
                value={question}
                onChange={(value) => setQuestion(value)}
            />
            <Button isPrimary onClick={callAIChat}>
                Enviar pregunta
            </Button>
            <div>
                <strong>Respuesta:</strong>
                <p>{answer}</p>
            </div>
            <Button isSecondary onClick={handleNewTitle}>
                New Title
            </Button>
        </PluginDocumentSettingPanel>
    );
};

registerPlugin("plugin-document-setting-panel-demo", {
    render: PluginDocumentSettingPanelDemo,
    icon: "palmtree",
});

