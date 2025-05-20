const mongoose = require('mongoose');

// Replace with your MongoDB connection string
const mongoURI = 'mongodb+srv://dyterljfederiz:akinlangangdb@cluster0.lgvaa4g.mongodb.net/?retryWrites=true&w=majority&appName=Cluster0';

mongoose.connect(mongoURI, {
    useNewUrlParser: true,
    useUnifiedTopology: true,
}).then(() => {
    console.log('Connected to MongoDB');
}).catch((error) => {
    console.error('Error connecting to MongoDB:', error);
});

// Define a schema for reviews
const reviewSchema = new mongoose.Schema({
    name: { type: String, required: true },
    rating: { type: Number, required: true, min: 1, max: 5 },
    message: { type: String, required: true },
    date: { type: Date, default: Date.now },
});

// Create a model for reviews
const Review = mongoose.model('Review', reviewSchema);

// Example: Add a new review
const addReview = async () => {
    const newReview = new Review({
        name: 'John Doe',
        rating: 5,
        message: 'Great service!',
    });

    try {
        const savedReview = await newReview.save();
        console.log('Review saved:', savedReview);
    } catch (error) {
        console.error('Error saving review:', error);
    }
};

// Call the function to add a review
addReview();