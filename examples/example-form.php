<?php
/**
 * Place this form in single or home for testing
 */
?>

<form method="POST" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" enctype="multipart/form-data">
<input type='hidden' name='action' value='email_with_attachment'>
<input type="hidden" name="email-with-attachment-nonce" value="<?php echo esc_attr( wp_create_nonce( 'email-with-attachment-nonce' ) ); ?>" />

<label for='name'>Name: </label>
<input type="text" name="name" >

<label for='email'>Email: </label>
<input type="text" name="email" >

<label for='message'>Message:</label>
<textarea name="message"></textarea>

<label for='uploaded_file'>Select A File To Upload:</label>
<input type="file" name="vip-documents">

<input type="submit" value="Submit" name='submit'>
</form>
