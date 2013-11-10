//
//  AppDelegate.h
//  prevnext
//
//  Created by Ben Stolovitz on 10/20/13.
//  Copyright (c) 2013 Ben Stolovitz. All rights reserved.
//

#import <Cocoa/Cocoa.h>
#include "MDBorderlessWindow.h"

@interface AppDelegate : NSObject <NSApplicationDelegate>

@property (assign) IBOutlet MDBorderlessWindow *window;
@property (assign) IBOutlet NSImageView *image;

@end
