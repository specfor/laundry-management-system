<div class="w-screen">
    <div class="bg-gradient-to-r from-blue-900 from-10% via-sky-600 via-30% to-blue-950 to-70% h-auto">
        <div class="p-2 grid grid-cols-2">
            <div class="flex items-center font-bold text-lg w-auto text-white antialiased drop-shadow-[0_1.2px_1.2px_rgba(0,0,0,0.8)]">
                <h3>Laundry Management System</h3>
            </div>
            <div class="justify-self-end">
                <div class="flex ">
                    <?php
                    foreach ($variableData['header'] as $headerItem) {
                        echo '<button class="p-2 font-bold text-sky-400 hover:text-white mx-2" onclick="' .
                         $headerItem["onclick"] . '">' . $headerItem["label"] . '</button>';
                    }
                    ?>
                </div>
            </div>
        </div>
    </div>
</div>